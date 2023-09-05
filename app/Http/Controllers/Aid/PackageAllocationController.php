<?php

namespace App\Http\Controllers\Aid;

use App\Http\Controllers\Controller;
use App\Models\PackageAllocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class PackageAllocationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index(Request $request)
    {
        $packageAllocations = PackageAllocation::orderBy('created_at', 'desc');

        $status = $request->input('status');

        if ($status) {
            $packageAllocations->where('status', $status);
        }

        $packageAllocations->when($request['title'], function ($q) use ($request) {
            $q->whereHas('package', function ($subQ) use ($request) {
                $subQ->where('title', 'like', '%' . $request['title'] . '%');
            });
        });

        $packageAllocations->with([
            'agent.user' => function ($query) {
                $query->select('id', 'first_name', 'last_name');
            },
            'helpSeeker.user' => function ($query) {
                $query->select('id', 'first_name', 'last_name');
            },
            'package' => function ($query) {
                $query->select('id', 'title');
            },
            'package.items.product' => function ($query) {
                $query->select('id', 'name');
            }]);

        if (Gate::allows('is-helper')) {
            $packageAllocations->whereHas('package', function ($query) {
                $query->where('helper_id', Auth::user()->helper->id);
            });
        } else if (Gate::allows('is-help-seeker')) {
            $packageAllocations->where('help_seeker_id', Auth::user()->helpSeeker->id);
        }

        $packageAllocations = $packageAllocations->paginate(10);

        $transformedAllocations = $packageAllocations->map(function ($allocation) {
            $allocation['agent_name'] = [
                'first_name' => $allocation->agent->user->first_name,
                'last_name' => $allocation->agent->user->last_name
            ];

            $allocation['help_seeker_name'] = [
                'first_name' => $allocation->helpSeeker->user->first_name,
                'last_name' => $allocation->helpSeeker->user->last_name
            ];

            foreach ($allocation['package']['items'] as $item) {
                unset($item['id']);
                unset($item['product_id']);
                unset($item['package_id']);
                unset($item['created_at']);
                unset($item['updated_at']);
            }

            unset($allocation->agent);
            unset($allocation->package_id);
            unset($allocation->helpSeeker);

            return $allocation;
        });

        return response()->json([
            'allocations' => $transformedAllocations,
            'count' => $packageAllocations->total(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        if (Gate::allows('is-manager-or-agent')) {
            $validatedData = $request->validate([
                'agent_id' => 'exists:agents,id',
                'status' => ['required|in:assigned,not_assigned'],
                'quantity' => 'required|integer',
                'help_seeker_id' => 'required|exists:help_seekers,id',
                'package_id' => 'required|exists:packages,id',
            ]);

            if (empty($validatedData['status'])) {
                $validatedData['status'] = 'not_assigned';
            }

            if (empty($validatedData['agent_id'])) {
                $validatedData['agent_id'] = auth()->user()->agent->id;
            }

            $packageAllocation = PackageAllocation::create($validatedData);

            return response()->json($packageAllocation);
        } else {
            return response()->json(['message' => 'Access denied.'], 403);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        if (Gate::allows('is-manager-or-agent')) {
            $validatedData = $request->validate([
                'agent_id' => 'exists:agents,id',
                'status' => ['in:assigned,not_assigned'],
                'quantity' => 'integer',
                'help_seeker_id' => 'exists:help_seekers,id',
                'package_id' => 'exists:packages,id',
            ]);

            $packageAllocation = PackageAllocation::findOrFail($id);

            $packageAllocation->update($validatedData);

            return response()->json($packageAllocation);
        } else {
            return response()->json(['message' => 'Access denied.'], 403);
        }
    }

    public function show($id): JsonResponse
    {
        if (Gate::allows('is-manager-or-agent')) {
            $packageAllocation = PackageAllocation::findOrFail($id);

            return response()->json($packageAllocation);
        } else {
            return response()->json(['message' => 'Access denied.'], 403);
        }
    }

    // public function destroy($id): JsonResponse
    // {
    //     if (Gate::allows('is-manager-or-agent')) {
    //         $packageAllocation = PackageAllocation::findOrFail($id);

    //         $packageAllocation->delete();

    //         return response()->json(null, 204);
    //     } else {
    //         return response()->json(['message' => 'Access denied.'], 403);
    //     }
    // }

    public function destroyPackageAllocations(Request $request)
    {
        if (Gate::allows('is-manager-or-agent')) {
            $allocationIds = $request->input('package_allocation_ids');
    
            if (!$allocationIds || !is_array($allocationIds)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid allocation_ids provided',
                ], 400);
            }
    
            $deletedAllocationIds = [];
            $notFoundAllocationIds = [];
    
            foreach ($allocationIds as $allocationId) {
                $allocation = PackageAllocation::find($allocationId);
    
                if (!$allocation) {
                    $notFoundAllocationIds[] = $allocationId;
                } else {
                    // Get the associated package and its items
                    $package = $allocation->package;
                    $packageItems = $package->items;
    
                    // Return quantities to products for each package item
                    foreach ($packageItems as $packageItem) {
                        $product = $packageItem->product;
    
                        if ($product) {
                            $product->quantity += $packageItem->quantity * $allocation->quantity;
                            $product->save();
                        }
                    }
    
                    $allocation->delete();
                    $deletedAllocationIds[] = $allocationId;
                }
            }
    
            $response = [
                'status' => 'success',
                'message' => 'Package allocations deleted successfully',
                'deleted_allocation_ids' => $deletedAllocationIds,
            ];
    
            if (!empty($notFoundAllocationIds)) {
                $response['not_found_allocation_ids'] = $notFoundAllocationIds;
            }
    
            return response()->json($response);
        } else {
            return response()->json(['message' => 'Access denied'], 403);
        }
    }   
}
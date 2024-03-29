<?php

namespace App\Http\Controllers\Aid;

use App\Http\Controllers\Controller;
use App\Models\AidAllocation;
use App\Models\PeopleAid;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class AidAllocationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $aidAllocations = AidAllocation::orderBy('created_at', 'desc');

        $aidStatus = $request['status'];

        if ($aidStatus) {
            $aidAllocations->where('status', $aidStatus);
        }

        $aidAllocations->when($request['help_seeker_id'], function ($q) use ($request) {
            $q->where('help_seeker_id', $request['help_seeker_id']);
        })->when($request['title'], function ($q) use ($request) {
            $q->whereHas('peopleAid', function ($subQ) use ($request) {
                $subQ->where('title', 'like', '%' . $request['title'] . '%');
            });
        });

        $aidAllocations->with([
            'agent.user' => function ($query) {
                $query->select('id', 'first_name', 'last_name');
            },
            'helpSeeker.user' => function ($query) {
                $query->select('id', 'first_name', 'last_name');
            },
            'peopleAid.helper.user' => function ($query) {
                $query->select('id', 'first_name', 'last_name');
            }
        ]);


        if (Gate::allows('is-helper')) {
            $aidAllocations->whereHas('peopleAid', function ($query) {
                $query->where('helper_id', Auth::user()->helper->id);
            });
        } else if (Gate::allows('is-help-seeker')) {
            $aidAllocations->where('help_seeker_id', Auth::user()->helpSeeker->id);
        }

        $count = $aidAllocations->count();
        $aidAllocations->paginate(10);

        $transformedAllocations = $aidAllocations->get()->map(function ($allocation) {
            $allocation['agent_name'] = [
                'first_name' => $allocation->agent->user->first_name,
                'last_name' => $allocation->agent->user->last_name
            ];

            $allocation['help_seeker_name'] = [
                'first_name' => $allocation->helpSeeker->user->first_name,
                'last_name' => $allocation->helpSeeker->user->last_name
            ];

            $allocation['helper_name'] = [
                'first_name' => $allocation->peopleAid->helper->user->first_name,
                'last_name' => $allocation->peopleAid->helper->user->last_name
            ];

            unset($allocation->agent);
            unset($allocation['people_aid_id']);
            unset($allocation->helpSeeker);
            unset($allocation->peopleAid->helper);

            return $allocation;
        });

        return response()->json([
            'allocations' => $transformedAllocations,
            'count' => $count,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        if (Gate::allows('is-manager-or-agent')) {
            $validatedData = $request->validate([
                'agent_id' => 'exists:agents,id',
                'status' => ['in:assigned,not_assigned'],
                'quantity' => 'required|integer',
                'help_seeker_id' => 'required|exists:help_seekers,id',
                'people_aid_id' => 'required|exists:people_aids,id',
            ]);

            if (empty($validatedData['status'])) {
                $validatedData['status'] = 'not_assigned';
            }

            if (empty($validatedData['agent_id'])) {
                $validatedData['agent_id'] = auth()->user()->agent->id;
            }

            $peopleAid = PeopleAid::where('id', $validatedData['people_aid_id'])->first();

            $deductFromPeopleAid = $peopleAid->quantity - $validatedData['quantity'];
            
            if ($deductFromPeopleAid >= 0) {
                $peopleAid->quantity = $deductFromPeopleAid;
                $peopleAid->save();
            } else {
                return response()->json(['message' => 'qauntity of people aid is not enough'], 422);
            }
            

            $aidAllocation = AidAllocation::create($validatedData);

            return response()->json($aidAllocation);
        } else {
            return response()->json(['message' => 'access denied'], 403);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        if (Gate::allows('is-manager-or-agent')) {
            $validatedData = $request->validate([
                'agent_id' => 'exists:agents,id',
                'status' => ['in:assigned,not_assigned'],
                'quantity' => 'integer',
                'help_seeker_id' => 'exists:help_seekers,id',
                'people_aid_id' => 'exists:people_aids,id',
            ]);

            $aidAllocation = AidAllocation::findOrFail($id);

            $aidAllocation->update($validatedData);

            return response()->json($aidAllocation);
        } else {
            return response()->json(['message' => 'access denied'], 403);
        }
    }

    public function show($id)
    {
        if (Gate::allows('is-manager-or-agent')) {
            $aidAllocation = AidAllocation::findOrFail($id);

            return response()->json($aidAllocation);
        } else {
            return response()->json(['message' => 'access denied'], 403);
        }
    }

    // public function destroy($id)
    // {
    //     if (Gate::allows('is-manager-or-agent')) {
    //         $aidAllocation = AidAllocation::findOrFail($id);

    //         $aidAllocation->delete();

    //         return response()->json(null, 204);
    //     } else {
    //         return response()->json(['message' => 'access denied'], 403);
    //     }
    // }

    public function destroyAidAllocations(Request $request)
    {
        if (Gate::allows('is-manager-or-agent')) {
            $allocationIds = $request->input('aid_allocation_ids');

            if (!$allocationIds || !is_array($allocationIds)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid allocation_ids provided',
                ], 400);
            }

            $deletedAllocationIds = [];
            $notFoundAllocationIds = [];

            foreach ($allocationIds as $allocationId) {
                $allocation = AidAllocation::find($allocationId);

                if (!$allocation) {
                    $notFoundAllocationIds[] = $allocationId;
                } else {
                    // Get the PeopleAid associated with the allocation
                    $peopleAid = PeopleAid::find($allocation->people_aid_id);

                    if ($peopleAid) {
                        // Return quantities to products
                        $product = Product::find($peopleAid->product_id);
                        
                        if ($product) {
                            $product->quantity += $allocation->quantity * $peopleAid->quantity;
                            $product->save();
                        }
                    }

                    $allocation->delete();
                    $deletedAllocationIds[] = $allocationId;
                }
            }

            $response = [
                'status' => 'success',
                'message' => 'Aid allocations deleted successfully',
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

    public function multiAssign(Request $request)
    {
        if (Gate::allows('is-manager-or-agent')) {
            $allocationIds = $request->input('aid_allocation_ids');

            if (!$allocationIds || !is_array($allocationIds)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid allocation_ids provided',
                ], 400);
            }

            $assignedAllocationIds = [];
            $notFoundAllocationIds = [];

            foreach ($allocationIds as $allocationId) {
                $allocation = AidAllocation::find($allocationId);

                if (!$allocation) {
                    $notFoundAllocationIds[] = $allocationId;
                } else {
                    $assignedAllocationIds[] = $allocationId;

                    $allocation->status = 'assigned';
                    $allocation->save();
                }
            }
            $response = [
                'status' => 'success',
                'message' => 'Aid allocations assigned successfully',
                'assigned_allocation_ids' => $assignedAllocationIds,
            ];

            if (!empty($notFoundAllocationIds)) {
                $response['not_found_allocation_ids'] = $notFoundAllocationIds;
            }

            return response()->json($response);
        } else {
            return response()->json(['message' => 'Access denied'], 403);
        }
    }

    public function chartData()
    {
        if (Gate::allows('is-manager-or-agent')) {
            $peopleAidsCount = PeopleAid::all()->count();

            $assignedAidAllocationCount = AidAllocation::where('status', 'assigned')->count();
            $notAssignedAidAllocationCount = AidAllocation::where('status', 'not_assigned')->count();

            return response()->json(["data" => [$peopleAidsCount, $assignedAidAllocationCount, $notAssignedAidAllocationCount]]);
        } else {
            return response()->json(['message' => 'Access denied'], 403);
        }
    }
}

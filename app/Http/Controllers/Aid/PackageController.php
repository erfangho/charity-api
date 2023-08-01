<?php

namespace App\Http\Controllers\Aid;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PackageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the packages.
     *
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        if (Gate::allows('is-manager-or-agent')) {
            $title = $request['title'];

            $query = Package::orderBy('created_at', 'desc');;

            $query->when($title, function ($q) use ($title) {
                $q->where('title', 'like', '%' . $title . '%');
            });

            $query->withCount('packageItems');

            $queryCount = $query->count();
            $query->paginate(10);

            return response()->json([
                'packages' => $query->get(),
                'count' => $queryCount
            ]);
        } else {
            $user = Auth::user();

            $packages = Package::all();

            if ($user->role == 'help_seeker') {
                $helpSeeker = $user->helpSeeker;

                return response()->json($helpSeeker->packageAllocations);
            } else {
                return response()->json(['message' => 'access denied'], 403);
            }
        }
    }

    /**
     * Store a newly created package in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        if (Gate::allows('is-manager-or-agent')) {
            $request->validate([
                'title' => 'required|string',
                'organization_id' => 'required|exists:organizations,id',
                'quantity' => 'required|integer',
                'description' => 'nullable|string',
            ]);

            $package = Package::create($request->all());

            return response()->json($package, 201);
        } else {
            return response()->json(['message' => 'access denied'], 403);
        }
    }

    /**
     * Display the specified package.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show($id)
    {
        $package = Package::findOrFail($id);

        return response()->json($package);
    }

    /**
     * Update the specified package in storage.
     *
     * @param Request $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        if (Gate::allows('is-manager-or-agent')) {
            $request->validate([
                'title' => 'string',
                'organization_id' => 'exists:organizations,id',
                'quantity' => 'integer',
                'description' => 'nullable|string',
            ]);

            $package = Package::findOrFail($id);
            $package->update($request->all());

            return response()->json($package);
        } else {
            return response()->json(['message' => 'access denied'], 403);
        }
    }

    /**
     * Remove the specified package from storage.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        if (Gate::allows('is-manager-or-agent')) {
            $package = Package::findOrFail($id);
            $package->delete();

            return response()->json(null, 204);
        } else {
            return response()->json(['message' => 'access denied'], 403);
        }
    }

    public function destroyPackages(Request $request)
    {
        if (Gate::allows('is-manager-or-agent')) {
            $packageIds = $request->input('package_ids');

            if (!$packageIds || !is_array($packageIds)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid package_ids provided',
                ], 400);
            }

            $deletedPackageIds = [];
            $notFoundPackageIds = [];

            foreach ($packageIds as $packageId) {
                $package = Package::find($packageId);

                if (!$package) {
                    $notFoundPackageIds[] = $packageId;
                } else {
                    $package->delete();
                    $deletedPackageIds[] = $packageId;
                }
            }

            $response = [
                'status' => 'success',
                'message' => 'Packages deleted successfully',
                'deleted_package_ids' => $deletedPackageIds,
            ];

            if (!empty($notFoundPackageIds)) {
                $response['not_found_package_ids'] = $notFoundPackageIds;
            }

            return response()->json($response);
        } else {
            return response()->json(['message' => 'Access denied'], 403);
        }
    }
}

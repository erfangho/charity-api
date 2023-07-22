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

            $query = Package::query();

            $query->when($title, function ($q) use ($title) {
                $q->where('title', 'like', '%' . $title . '%');
            });

            $query->withCount('packageItems');

            $query->paginate(10);

            return response()->json([
                'packages' => $query->get(),
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
}

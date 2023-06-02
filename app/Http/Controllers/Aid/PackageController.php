<?php

namespace App\Http\Controllers\Aid;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
    public function index()
    {
        $packages = Package::all();

        return response()->json($packages);
    }

    /**
     * Store a newly created package in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'organization_id' => 'required|exists:organizations,id',
            'quantity' => 'required|integer',
            'description' => 'nullable|string',
        ]);

        $package = Package::create($request->all());

        return response()->json($package, 201);
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
        $request->validate([
            'title' => 'string',
            'organization_id' => 'exists:organizations,id',
            'quantity' => 'integer',
            'description' => 'nullable|string',
        ]);

        $package = Package::findOrFail($id);
        $package->update($request->all());

        return response()->json($package);
    }

    /**
     * Remove the specified package from storage.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        $package = Package::findOrFail($id);
        $package->delete();

        return response()->json(null, 204);
    }
}

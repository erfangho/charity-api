<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OrganizationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the organizations.
     *
     * @return JsonResponse
     */
    public function index()
    {
        $organizations = Organization::all();

        return response()->json($organizations);
    }

    /**
     * Store a newly created organization in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'phone_number' => 'required|string',
            'description' => 'string',
            'address' => 'required|string',
        ]);

        $organization = Organization::create($request->all());

        return response()->json($organization, 201);
    }

    /**
     * Display the specified organization.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show($id)
    {
        $organization = Organization::findOrFail($id);

        return response()->json($organization);
    }

    /**
     * Update the specified organization in storage.
     *
     * @param Request $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string',
            'phone_number' => 'required|string',
            'description' => 'string',
            'address' => 'required|string',
        ]);

        $organization = Organization::findOrFail($id);
        $organization->update($request->all());

        return response()->json($organization);
    }

    /**
     * Remove the specified organization from storage.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        $organization = Organization::findOrFail($id);
        $organization->delete();

        return response()->json(null, 204);
    }
}

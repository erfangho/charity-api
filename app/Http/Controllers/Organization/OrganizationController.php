<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

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
        if (Gate::allows('is-manager-or-agent')) {
            $organizations = Organization::all();

            return response()->json($organizations);
        } else {
            return response()->json(['message' => 'Access denied'], 403);
        }
    }

    /**
     * Store a newly created organization in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        if (Gate::allows('is-manager-or-agent')) {
            $request->validate([
                'name' => 'required|string',
                'phone_number' => 'required|string',
                'description' => 'string',
                'address' => 'required|string',
            ]);

            $organization = Organization::create($request->all());

            return response()->json($organization, 201);
        } else {
            return response()->json(['message' => 'Access denied'], 403);
        }
    }

    /**
     * Display the specified organization.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show($id)
    {
        if (Gate::allows('is-manager-or-agent')) {
            $organization = Organization::findOrFail($id);

            return response()->json($organization);
        } else {
            return response()->json(['message' => 'Access denied'], 403);
        }
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
        if (Gate::allows('is-manager-or-agent')) {
            $request->validate([
                'name' => 'string',
                'phone_number' => 'string',
                'description' => 'string',
                'address' => 'string',
            ]);

            $organization = Organization::findOrFail($id);
            $organization->update($request->all());

            return response()->json($organization);
        } else {
            return response()->json(['message' => 'Access denied'], 403);
        }
    }

    /**
     * Remove the specified organization from storage.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        if (Gate::allows('is-manager-or-agent')) {
            $organization = Organization::findOrFail($id);
            $organization->delete();

            return response()->json(null, 204);
        } else {
            return response()->json(['message' => 'Access denied'], 403);
        }
    }
}

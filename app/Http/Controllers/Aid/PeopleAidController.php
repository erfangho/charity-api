<?php

namespace App\Http\Controllers\Aid;

use App\Http\Controllers\Controller;
use App\Models\PeopleAid;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class PeopleAidController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the people aids.
     *
     * @return JsonResponse
     */
    public function index()
    {
        if (Gate::allows('is-manager-or-agent')) {
            $peopleAids = PeopleAid::all();

            return response()->json($peopleAids);
        } else {
            $user = Auth::user();

            $peopleAids = PeopleAid::all();

            if ($user->role == 'help_seeker') {
                $helpSeeker = $user->helpSeeker;

                return response()->json($helpSeeker->aidAllocations);
            } else {
                return response()->json(['message' => 'Access denied'], 403);
            }
        }
    }

    /**
     * Store a newly created people aid in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        if (Gate::allows('is-manager-or-agent')) {
            $request->validate([
                'title' => 'required|string',
                'product_id' => 'required|exists:products,id',
                'helper_id' => 'required|exists:helpers,id',
                'quantity' => 'required|integer',
                'description' => 'nullable|string',
            ]);

            $peopleAid = PeopleAid::create($request->all());

            return response()->json($peopleAid, 201);
        } else {
            return response()->json(['message' => 'Access denied'], 403);
        }
    }

    /**
     * Display the specified people aid.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show($id)
    {
        if (Gate::allows('is-manager-or-agent')) {
            $peopleAid = PeopleAid::findOrFail($id);

            return response()->json($peopleAid);
        } else {
            return response()->json(['message' => 'Access denied'], 403);
        }
    }

    /**
     * Update the specified people aid in storage.
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
                'product_id' => 'exists:products,id',
                'helper_id' => 'exists:helpers,id',
                'quantity' => 'integer',
                'description' => 'nullable|string',
            ]);

            $peopleAid = PeopleAid::findOrFail($id);
            $peopleAid->update($request->all());

            return response()->json($peopleAid);
        } else {
            return response()->json(['message' => 'Access denied'], 403);
        }
    }

    /**
     * Remove the specified people aid from storage.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        if (Gate::allows('is-manager-or-agent')) {
            $peopleAid = PeopleAid::findOrFail($id);
            $peopleAid->delete();

            return response()->json(null, 204);
        } else {
            return response()->json(['message' => 'Access denied'], 403);
        }
    }
}
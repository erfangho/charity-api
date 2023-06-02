<?php

namespace App\Http\Controllers\Aid;

use App\Http\Controllers\Controller;
use App\Models\PeopleAid;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
        $peopleAids = PeopleAid::all();

        return response()->json($peopleAids);
    }

    /**
     * Store a newly created people aid in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'product_id' => 'required|exists:products,id',
            'helper_id' => 'required|exists:helpers,id',
            'quantity' => 'required|integer',
            'description' => 'nullable|string',
        ]);

        $peopleAid = PeopleAid::create($request->all());

        return response()->json($peopleAid, 201);
    }

    /**
     * Display the specified people aid.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show($id)
    {
        $peopleAid = PeopleAid::findOrFail($id);

        return response()->json($peopleAid);
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
    }

    /**
     * Remove the specified people aid from storage.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        $peopleAid = PeopleAid::findOrFail($id);
        $peopleAid->delete();

        return response()->json(null, 204);
    }
}

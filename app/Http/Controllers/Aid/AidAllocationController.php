<?php

namespace App\Http\Controllers\Aid;

use App\Http\Controllers\Controller;
use App\Models\AidAllocation;
use Illuminate\Http\Request;

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
            'count' => $aidAllocations->count(),
        ]);
    }
}

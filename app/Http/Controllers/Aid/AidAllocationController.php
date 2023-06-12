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

        $aidAllocations->with('agent', 'helpSeeker', 'peopleAid');

        $aidAllocations->paginate(10);

        return response()->json([
            'allocations' => $aidAllocations->get(),
            'count' => $aidAllocations->count(),
        ]);
    }
}

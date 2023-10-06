<?php

namespace App\Http\Controllers\Aid;

use App\Http\Controllers\Controller;
use App\Models\AidAllocation;
use App\Models\PeopleAid;
use App\Models\Product;
use Carbon\Carbon;
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
    public function index(Request $request)
    {
        if (Gate::allows('is-manager-or-agent')) {
            $title = $request['title'];
            $startHour = $request['start_hour'];
            $endHour = $request['end_hour'];
            $startDate = $request['start_date'];
            $endDate = $request['end_date'];
            $aidType = $request['type'];

            $query = PeopleAid::orderBy('created_at', 'desc');;

            if ($title) {
                $query->where('title', 'like', '%' . $title . '%');
            }

            if ($startDate && $endDate) {
                if ($startHour && $endHour) {
                    $startDateTime = Carbon::parse($startDate . ' ' . $startHour);
                    $endDateTime = Carbon::parse($endDate . ' ' . $endHour);

                    $query->whereBetween('created_at', [$startDateTime, $endDateTime]);
                } else {
                    $startDateTime = Carbon::parse($startDate )->startOfDay();
                    $endDateTime = Carbon::parse($endDate)->endOfDay();

                    $query->whereBetween('created_at', [$startDateTime, $endDateTime]);
                }
            } elseif (empty($startDate) && empty($endDate)) {
                if ($startHour && $endHour) {
                    $startDateTime = Carbon::parse($startHour);
                    $endDateTime = Carbon::parse($endHour);

                    $query->whereBetween('created_at', [$startDateTime, $endDateTime]);
                }
            }

            if ($aidType) {
                $query->whereHas('product', function ($query) use ($aidType) {
                    $query->where('type', $aidType);
                });

                if ($aidType == 'cash') {
                    $peopleAidsCount = $query->sum('quantity');
                } else if ($aidType == 'product') {
                    $peopleAidsCount = $query->count();
                }
            } else {
                $peopleAidsCount = $query->count();
            }
            
            $query->with([
                'helper.user' => function ($query) {
                    $query->select('id', 'first_name', 'last_name');
                },
            ]);

            $query->with('product');

            $peopleAids = $query->paginate(10);

            $transformedAllocations = $peopleAids->map(function ($allocation) {
                $allocation['helper_name'] = [
                    'first_name' => $allocation->helper->user->first_name,
                    'last_name' => $allocation->helper->user->last_name
                ];

                unset($allocation->agent);
                unset($allocation->helper);

                return $allocation;

            });

            return response()->json([
                'peopleAids' => $transformedAllocations,
                'count' => $peopleAidsCount,
            ]);
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
            
            $product = Product::where('name', $request['title'])->first();

            if ($product) {
                $peopleAid = PeopleAid::create($request->all());

                $product->quantity += $request['quantity'];
                $product->update();
            } else {
                return response()->json(['message' => 'There is no product with this name'], 422);
            }


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

    public function destroyPeopleAids(Request $request)
    {
        if (Gate::allows('is-manager-or-agent')) {
            $peopleAidIds = $request->input('people_aid_ids');

            if (!$peopleAidIds || !is_array($peopleAidIds)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid people_aid_ids provided',
                ], 400);
            }

            $deletedPeopleAidIds = [];
            $notFoundPeopleAidIds = [];

            foreach ($peopleAidIds as $peopleAidId) {
                $peopleAid = PeopleAid::find($peopleAidId);

                if (!$peopleAid) {
                    $notFoundPeopleAidIds[] = $peopleAidId;
                } else {
                    $peopleAid->delete();
                    $deletedPeopleAidIds[] = $peopleAidId;
                }
            }

            $response = [
                'status' => 'success',
                'message' => 'People Aid records deleted successfully',
                'deleted_people_aid_ids' => $deletedPeopleAidIds,
            ];

            if (!empty($notFoundPeopleAidIds)) {
                $response['not_found_people_aid_ids'] = $notFoundPeopleAidIds;
            }

            return response()->json($response);
        } else {
            return response()->json(['message' => 'Access denied'], 403);
        }
    }

    public function abundanceChart()
    {
        if (Gate::allows('is-manager-or-agent')) {
            $peopleAidData = PeopleAid::getMonthlyCountsLastYear();
            $aidAllocationData = AidAllocation::getMonthlyCountsLastYear();

            return response()->json([
                'people_aid_data' => $peopleAidData,
                'aid_allocation_data' => $aidAllocationData
            ]);
        } else {
            return response()->json(['message' => 'Access denied'], 403);
        }
    }

    public function getHelperAidHistory(Request $request)
    {
        if (Gate::allows('is-manager-or-agent')) {
            if ($request->has('helper_id')) {
                $validatedRequest = $request->validate([
                    'helper_id' => 'required|exists:helpers,id',
                ]);
    
                $peopleAids = PeopleAid::where('helper_id', $validatedRequest['helper_id'])->get();

                $helperAids = $peopleAids->map(function ($peopleAid) {
                    return [
                        'title' => $peopleAid->title,
                        'quantity' => $peopleAid->quantity,
                        'created_at' => $peopleAid->created_at,
                    ];
                });

                return response()->json($helperAids);
            } else if ($request->has('help_seeker_id')) {
                $validatedRequest = $request->validate([
                    'help_seeker_id' => 'required|exists:help_seekers,id',
                ]);

                $aidAllocations = AidAllocation::where('help_seeker_id', $validatedRequest['help_seeker_id'])->get();

                $helpSeekerAids = $aidAllocations->map(function ($aidAllocation) {
                    return [
                        'title' => $aidAllocation->peopleAid->title,
                        'quantity' => $aidAllocation->quantity,
                        'created_at' => $aidAllocation->created_at,
                    ];
                });

                return response()->json($helpSeekerAids);
            }
        } else {
            return response()->json(['message' => 'Access denied'], 403);
        }
    }
}

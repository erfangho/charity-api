<?php

namespace App\Http\Controllers\Aid;

use App\Http\Controllers\Controller;
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
            $startHour = $request['start_hour'];
            $endHour = $request['end_hour'];
            $startDate = $request['start_date'];
            $endDate = $request['end_date'];
            $aidType = $request['type'];

            $query = PeopleAid::query();

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
}

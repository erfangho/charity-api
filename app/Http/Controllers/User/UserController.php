<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index(Request $request)
    {
        if (Gate::allows('is-manager-or-agent')) {
            $query = User::orderBy('created_at', 'desc');

        $userRole = $request['role'];
        $first_name = $request['first_name'];
        $last_name = $request['last_name'];
        $national_code = $request['national_code'];

        if ($userRole) {
            $query->where('role', $userRole);
        }

        $query->when($userRole == 'helper', function ($q) {
            $q->with(['helper.peopleAids.product']);
        });

        $query->when($userRole == 'help_seeker', function ($q) {
            $q->with(['helpSeeker.aidAllocations.peopleAid']);
        });

        if ($first_name == $last_name) {
            $query->when($first_name, function ($q) use ($first_name) {
                $q->where(function ($query) use ($first_name) {
                    $query->where('first_name', 'like', '%' . $first_name . '%')
                          ->orWhere('last_name', 'like', '%' . $first_name . '%');
                });
            });
        } else {
            $query->when($first_name, function ($q) use ($first_name) {
                $q->where('first_name', 'like', '%' . $first_name. '%');
            });
    
            $query->when($last_name, function ($q) use ($last_name) {
                $q->where('last_name', 'like', '%' . $last_name. '%');
            });
        }

        $query->when($national_code, function ($q) use ($national_code) {
            $q->where('national_code', 'like', '%' . $national_code. '%');
        });
        
        $totalCountQuery = $query->count();

        $users = $query->paginate(10);
            if ($userRole == 'helper') {
                $users->each(function ($user) {
                    $totalCash = $user->helper->peopleAids->filter(function ($peopleAid) {
                        return $peopleAid->product->type === 'cash';
                    })->sum('quantity');

                    $totalProduct = $user->helper->peopleAids->filter(function ($peopleAid) {
                        return $peopleAid->product->type === 'product';
                    })->sum('quantity');
        
                    $user->totalCash = $totalCash;
                    $user->totalProduct = $totalProduct;
                    unset($user->helper);
                });

                $users = $users->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'username' => $user->username,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'national_code' => $user->national_code,
                        'email' => $user->email,
                        'phone_number' => $user->phone_number,
                        'role' => $user->role,
                        'address' => $user->address,
                        'total_cash' => isset($user->totalCash) ? $user->totalCash : null,
                        'total_product' => isset($user->totalProduct) ? $user->totalProduct : null,
                    ];
                });
            } else if ($userRole == 'help_seeker') {
                $users->each(function ($user) {
                    $totalCashAllocated = $user->helpSeeker->aidAllocations->filter(function ($aidAllocations) {
                        return $aidAllocations->peopleAid->product->type === 'cash';
                    })->sum('quantity');

                    $totalProductAllocated = $user->helpSeeker->aidAllocations->filter(function ($aidAllocations) {
                        return $aidAllocations->peopleAid->product->type === 'product';
                    })->sum('quantity');
        
                    $user->totalCashAllocated = $totalCashAllocated;
                    $user->totalProductAllocated = $totalProductAllocated;
                    unset($user->helpSeekr);
                });

                $users = $users->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'username' => $user->username,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'national_code' => $user->national_code,
                        'email' => $user->email,
                        'phone_number' => $user->phone_number,
                        'role' => $user->role,
                        'address' => $user->address,
                        'total_cash_allocated' => isset($user->totalCashAllocated) ? $user->totalCashAllocated : null,
                        'total_product_allocated' => isset($user->totalProductAllocated) ? $user->totalProductAllocated : null,
                    ];
                });
            } else {
                $users = $query->get();
            }

            return response()->json([
                'users' => $users,
                'count' => $totalCountQuery,
            ]);
        } else {
            return response()->json(['message' => 'Access denied'], 403);
        }
    }

    public function show($id)
    {
        if (Gate::allows('is-manager-or-agent')) {
            $user = User::find($id);

            if ($user->role == 'manager') {
                $user->manager;
            } else if ($user->role == 'agent') {
                $user->agent;
            } else if ($user->role == 'helper') {
                $user->helper;
            } else if ($user->role == 'help_seeker') {
                $user->helpSeeker;
            }

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found',
                ], 404);
            }

            return response()->json([
            'status' => 'success',
                'user' => $user,
            ]);
        } else {
            return response()->json(['message' => 'Access denied'], 403);
        }
    }

    public function update(Request $request, $id)
    {
        if (Gate::allows('is-manager-or-agent')) {
            $user = User::find($id);
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found',
                ], 404);
            }

            $validatedData = $request->validate([
                'username' => 'string|unique:users,username,' . $id,
                'first_name' => 'string',
                'last_name' => 'string',
                'national_code' => 'string|unique:users,national_code,' . $id,
                'phone_number' => 'string|unique:users,phone_number,' . $id,
                'email' => 'string|unique:users,email,' . $id,
                'address' => 'string',
                'password' => 'string',
                'role' => 'string',
            ]);

            $user->update($validatedData);

            return response()->json([
            'status' => 'success',
                'message' => 'User updated successfully',
                'user' => $user,
            ]);
        } else {
            return response()->json(['message' => 'Access denied'], 403);
        }
    }

    public function destroy($id)
    {
        if (Gate::allows('is-manager-or-agent')) {
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found',
                ], 404);
            }

            $user->delete();

            return response()->json([
            'status' => 'success',
                'message' => 'User deleted successfully',
            ]);
        } else {
            return response()->json(['message' => 'Access denied'], 403);
        }
    }
}

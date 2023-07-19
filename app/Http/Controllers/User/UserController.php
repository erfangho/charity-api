<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index(Request $request)
    {
        $query = User::query();

        $userRole = $request['role'];

        if ($userRole) {
            $query->where('role', $userRole);
        }

        $query->when($userRole == 'helper', function ($q) {
            $q->with(['helper.peopleAids.product']);
        });
    
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
                    'phone_number' => $user->phone_number,
                    'total_cash' => isset($user->totalCash) ? $user->totalCash : null,
                    'total_product' => isset($user->totalProduct) ? $user->totalProduct : null,
                ];
            });
        } else {
            $users = $query->get();
        }

        return response()->json([
            'users' => $users,
            'count' => $users->count(),
        ]);
    }

    public function show($id)
    {
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
    }

    public function update(Request $request, $id)
    {
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
    }

    public function destroy($id)
    {
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
    }
}

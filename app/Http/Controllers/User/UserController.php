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

    public function index()
    {
        $users = User::all();

        return response()->json([
            'users' => $users,
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

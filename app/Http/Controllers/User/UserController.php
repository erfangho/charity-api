<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();

        return response()->json([
            'status' => 'success',
            'users' => $users,
        ]);
    }

    public function show($id)
    {
        $user = User::find($id);

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
            'username' => 'required|string|unique:users,username,' . $id,
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'national_code' => 'required|string|unique:users,national_code,' . $id,
            'phone_number' => 'required|string|unique:users,phone_number,' . $id,
            'email' => 'required|string|unique:users,email,' . $id,
            'address' => 'required|string',
            'password' => 'required|string',
            'role' => 'required|string',
        ]);

        $user->username = $validatedData['username'];
        $user->first_name = $validatedData['first_name'];
        $user->last_name = $validatedData['last_name'];
        $user->national_code = $validatedData['national_code'];
        $user->phone_number = $validatedData['phone_number'];
        $user->email = $validatedData['email'];
        $user->address = $validatedData['address'];
        $user->password = $validatedData['password'];
        $user->role = $validatedData['role'];

        $user->save();

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

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Helper;
use App\Models\HelpSeeker;
use App\Models\Manager;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;


class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login']]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'national_code' => 'required|string',
            'password' => 'required|string',
        ]);
        $credentials = $request->only('national_code', 'password');

        $token = Auth::attempt($credentials);
        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }

        $user = Auth::user();
        return response()->json([
            'status' => 'success',
            'user' => $user,
            'authorization' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);

    }

    public function register(Request $request, $role){
        if ($role == 'manager' and $request['role'] == 'manager') {
            if (Auth::user()->role == 'manager') {
                $newUser = $this->registerUser($request);

                $newManager = Manager::create([
                    'organization_id' => $request['organization_id'],
                    'user_id' => $newUser['id'],
                ]);

                $newUserData = [
                    'user' => $newUser,
                    'manager' => $newManager,
                ];

            } else {
                return response()->json([], 403);
            }
        } else if ($role == 'agent' and $request['role'] == 'agent') {
            if (Gate::allows('is-manager-or-agent')) {
                $newUser = $this->registerUser($request);

                $newAgent = Agent::create([
                    'organization_id' => $request['organization_id'],
                    'user_id' => $newUser['id'],
                ]);

                $newUserData = [
                    'user' => $newUser,
                    'agent' => $newAgent,
                ];

            } else {
                return response()->json([], 403);
            }
        } else if ($role == 'helper' and $request['role'] == 'helper') {
            if (Gate::allows('is-manager-or-agent')) {
                $newUser = $this->registerUser($request);

                if (Auth::user()->role == 'manager') {
                    $manager_id = Auth::user()->manager->id;

                    $newHelper = Helper::create([
                        'manager_id' => $manager_id,
                        'user_id' => $newUser['id'],
                    ]);
                } else {
                    $agent_id = Auth::user()->agent->id;

                    $newHelper = Helper::create([
                        'agent_id' => $agent_id,
                        'user_id' => $newUser['id'],
                    ]);
                }


                $newUserData = [
                    'user' => $newUser,
                    'agent' => $newHelper,
                ];

            } else {
                return response()->json([], 403);
            }
        } else if ($role == 'help_seeker' and $request['role'] == 'help_seeker') {
            if (Auth::user()->role == 'agent') {
                $newUser = $this->registerUser($request);

                $newHelpSeeker = HelpSeeker::create([
                    'agent_id' => Auth::user()->agent->id,
                    'user_id' => $newUser['id'],
                    'rate' => $request['rate'],
                ]);

                $newUserData = [
                    'user' => $newUser,
                    'help_seeker' => $newHelpSeeker,
                ];

            } else {
                return response()->json([], 403);
            }
        } else {
            return response()->json([], 404);
        }


        $token = Auth::login($newUserData['user']);

        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'details' => $newUserData,
            'authorization' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);
    }

    public function logout()
    {
        Auth::logout();
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ]);
    }

    public function refresh()
    {
        return response()->json([
            'status' => 'success',
            'user' => Auth::user(),
            'authorization' => [
                'token' => Auth::refresh(),
                'type' => 'bearer',
            ]
        ]);
    }

    private function registerUser(Request $request)
    {
        $validatedData = $request->validate([
            'username' => 'required|string|unique:users,username',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'national_code' => 'required|string|unique:users,national_code',
            'phone_number' => 'required|string|unique:users,phone_number',
            'email' => 'required|string|unique:users,email',
            'address' => 'required|string',
            'password' => 'required|string',
            'role' => 'required|string',
        ]);

        $request->except('organization_id', 'rate');

        $user = User::create([
            'username' => $validatedData['username'],
            'first_name' => $validatedData['first_name'],
            'last_name' => $validatedData['last_name'],
            'national_code' => $validatedData['national_code'],
            'phone_number' => $validatedData['phone_number'],
            'email' => $validatedData['email'],
            'address' => $validatedData['address'],
            'password' => $validatedData['password'],
            'role' => $validatedData['role'],
        ]);

        return $user;
    }

    public function getUserByToken(Request $request)
    {
        $user = Auth::user();

        if ($user->role == 'manager') {
            $user->manager;
        } else if ($user->role == 'agent') {
            $user->agent;
        } else if ($user->role == 'helper') {
            $user->helper;
        } else if ($user->role == 'help_seeker') {
            $user->helpSeeker;
        }

        return response()->json([
            'status' => 'success',
            'user' => $user,
        ]);
    }
}

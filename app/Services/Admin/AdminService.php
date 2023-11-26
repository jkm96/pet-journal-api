<?php

namespace App\Services\Admin;

use App\Exceptions\CustomException;
use App\Http\Resources\AdminResource;
use App\Http\Resources\UserResource;
use App\Models\Admin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminService
{
    /**
     * @param $request
     * @return JsonResponse
     */
    public function registerAdmin($request)
    {
        $user = Admin::create([
            'username' => $request['username'],
            'email' => $request['email'],
            'password' => Hash::make($request['password']),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Registered successfully',
            'user' => new AdminResource($user)
        ],200);
    }

    /**
     * @return AnonymousResourceCollection
     * @throws CustomException
     */
    public function getAllAdmins()
    {
        $users = Admin::all();
        if ($users->isEmpty())
            throw new CustomException("No admins were found", 404);

        return UserResource::collection($users);
    }

    /**
     * @param $loginRequest
     * @return JsonResponse
     * @throws CustomException
     */
    public function loginAdmin($loginRequest)
    {
        $emailOrUsername = $loginRequest["username"];
        $admin = Admin::where('email', $emailOrUsername)
            ->orWhere('username',$emailOrUsername)
            ->first();

        if ($admin == null)
            throw new CustomException("Login information is invalid - null.", 200);

        if (Hash::check($loginRequest["password"], $admin->password)){
            $token = $admin->createToken('authToken')->plainTextToken;
            $admin->auth_token = $token;
            $admin->update();

            return response()->json([
                'success' => true,
                'message' => "logged in successfully",
                'accessToken' => $token,
                'tokenType' => 'Bearer'
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Login information is invalid.'
        ], 200);
    }

    /**
     * @return JsonResponse
     */
    public function logoutAdmin()
    {
        dd(Auth::guard('admin')->check());
        if (request()->user('admin-api')->currentAccessToken() != null)
            request()->user('admin-api')->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message'=>'logged out successfully'
        ]);
    }
}

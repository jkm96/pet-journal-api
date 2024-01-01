<?php

namespace App\Services\Admin;

use App\Exceptions\CustomException;
use App\Http\Resources\AdminResource;
use App\Http\Resources\UserResource;
use App\Models\Admin;
use App\Utils\Helpers\ResponseHelpers;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthAdminService
{
    /**
     * @param $request
     * @return JsonResponse
     */
    public function registerAdmin($request)
    {
        try {
            $user = Admin::create([
                'username' => $request['username'],
                'email' => $request['email'],
                'password' => Hash::make($request['password']),
            ]);

            return ResponseHelpers::ConvertToJsonResponseWrapper(new AdminResource($user), 'Registered successfully', 200);
        } catch (\Exception $e) {
            return ResponseHelpers::ConvertToJsonResponseWrapper(['error' => $e->getMessage()], 'Error during admin registration', 500);
        }
    }

    /**
     * @param $loginRequest
     * @return JsonResponse
     */
    public function loginAdmin($loginRequest)
    {
        try {
            $emailOrUsername = $loginRequest["username"];
            $admin = Admin::where('email', $emailOrUsername)
                ->orWhere('username',$emailOrUsername)
                ->first();

            if ($admin == null)
                return ResponseHelpers::ConvertToJsonResponseWrapper([], 'Login information is invalid.', 400);

            if (Hash::check($loginRequest["password"], $admin->password)){
                $token = $admin->createToken('auth-token-' . $admin->username, ['*'], Carbon::now()->addHours(12))->plainTextToken;
                $admin->auth_token = $token;
                $admin->update();

                $adminResource = [
                    'token'=> $token,
                    'admin'=> new AdminResource($admin)
                ];

                return ResponseHelpers::ConvertToJsonResponseWrapper($adminResource, 'logged in successfully', 200);
            }

            return ResponseHelpers::ConvertToJsonResponseWrapper([], 'Login information is invalid.', 400);
        } catch (\Exception $e) {
            return ResponseHelpers::ConvertToJsonResponseWrapper(['error' => $e->getMessage()], 'Error during admin login', 500);
        }
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

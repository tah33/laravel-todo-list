<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api',['only' => ['logout']]);
    }

    public function showLoginError(): \Illuminate\Http\JsonResponse
    {
        return response()->json(['error' => 'Not authorized, Please Login First'],401);
    }
    public function login(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255|exists:users,email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }


        try {
            $user = User::where('email', $request->email)->first();

            if (!Hash::check($request->password, $user->password)) {
                // $this->warningLog($user->full_name .' Trying to Logged In with Incorrect Password');
                return response()->json(['error' => 'Invalid Credential']);
            }

            $credentials = $request->only('email', 'password');
            $token = JWTAuth::attempt($credentials);
            Auth::attempt($credentials);

            if (!$token) {
                return response()->json([
                    'status'    => 'error',
                    'error'     => 'Unauthorized',
                    'message'   => 'Unauthorized',
                ], 401);
            }

            return response()->json([
                'success'   => 'Logged In Successfully',
                'user'      => authData($user, $token),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function logout(): \Illuminate\Http\JsonResponse
    {
        Auth::logout();
        JWTAuth::invalidate(JWTAuth::getToken());
        session()->flush();
        session()->regenerate();

        return response()->json([
            'status'    => 'success',
            'success'   => true,
            'message'   => 'Successfully logged out',
        ]);
    }

    public function register(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required',
            'email'    => 'email|unique:users,email',
            'password' => 'required|confirmed|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {

            $data                       = $request->all();
            $data['password']           = bcrypt($request->password);
            $data['status']             = 1;
            $data['email_verified_at']  = now();

            $user = User::create($data);
            $credentials = $request->only('email', 'password');

            $token = JWTAuth::attempt($credentials);
            Auth::attempt($credentials);

            if (!$token) {
                return response()->json([
                    'status'    => 'error',
                    'error'     => 'Unauthorized',
                    'message'   => 'Unauthorized',
                ], 401);
            }

            DB::commit();
            return response()->json([
                'success'   => 'Logged In Successfully',
                'user'      => authData($user, $token),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()]);
        }
    }
}

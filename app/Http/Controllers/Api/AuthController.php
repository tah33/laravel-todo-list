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

    public function socialLogin(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'email'         => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            DB::beginTransaction();

            $user = User::where('email', $request->email)->where('user_type', 3)->first();

            if ($user)
            {
                if ($user->status == 0) {
                    // $this->warningLog($user->full_name .' Trying to Logged In with Pending Account');
                    return response()->json(['error' => 'Your Account is in Pending Mode']);
                }
            }
            else{
                $validator = Validator::make($request->all(), [
                    'first_name'    => 'required',
                    'last_name'     => 'required',
                    'email'         => 'required',
                ]);

                if ($validator->fails()) {
                    return response()->json(['errors' => $validator->errors()], 422);
                }

                $credentials = [
                    'first_name'        => $request->first_name,
                    'last_name'         => $request->last_name,
                    'email'             => $request->email,
                    'email_verified_at' => now(),
                    'password'          => bcrypt(rand(10000000, 99999999)),
                    'status'            => 1,
                ];

                $user = User::create($credentials);
            }

            DB::commit();
            try {
                if (!$token = JWTAuth::fromUser($user)) :
                    return response()->json([
                        'error' => __('Invalid credentials')
                    ],401);
                endif;
            } catch (JWTException $e) {
                DB::rollBack();
                return response()->json([
                    'error' => __('Unable to login, please try again')
                ],401);

            }catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'error' => __('Something went wrong')
                ],401);
            }

            return response()->json([
                'success' => 'Logged In Successfully',
                'user' => authData($user, $token),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Something Went Wrong']);
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

            User::create($data);

            DB::commit();
            return response()->json([
                'success' =>  'Account Created'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()]);
        }
    }
}

<?php

use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

if (!function_exists('authData')) {
    function authData($user, $token = null): array
    {
        $data = [
            'id'            => $user->id,
            'name'          => $user->name,
            'phone'         => $user->phone,
            'email'         => $user->email,
        ];

        if ($token) {
            $data['token'] = $token;
        }

        return $data;
    }
}

if (!function_exists('jwtUser'))
{
    function jwtUser()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (\Exception $e) {
            return null;
        }
        return $user;
    }
}

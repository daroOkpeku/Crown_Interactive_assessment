<?php

use App\Models\Admin;
use App\Models\Manifest;
use App\Models\Passport;

use App\Models\User;
use Carbon\Carbon;










if (!function_exists('apiResponse')) {

    function apiResponse(int $code, string $message,  $data = null)
    {

        if ($code === 200 || $code === 201) {
            return response()->json([
                'status' => true,
                "message" => $message,
                'data' => $data
            ], $code);
        }

        return response()->json([
            "error" => $message
        ], $code);
    }
}

if (!function_exists('apiResponseError')) {

    function apiResponseError(string $message, int $code = 400)
    {
        return response()->json([
            'status' => false,
            "message" => $message,
        ], $code);
    }
}

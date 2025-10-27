<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AfroMessageService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class OTPController extends Controller
{

    public function __construct(AfroMessageService $afroMessageService)
    {
        $this->afroMessageService = $afroMessageService;
    }

    public function send(Request $request)
    {
        $request->validate([
            'to' => 'required|string'
        ]);

        $result = $this->afroMessageService->sendOTP(
            $request->to,
            6,
            2,
            300,
            'Your code is "',
            '" Use it within 5 minutes'
        );

        return response()->json($result);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'to' => 'nullable|string',
            'verification_id' => 'nullable|string'
        ]);

        $result = $this->afroMessageService->verifyOTP(
            $request->code,
            $request->to,
            $request->verification_id
        );

        if (
            $result['http_code'] === 200 &&
            ($result['result']['acknowledge'] ?? '') === 'success'
        ) {
            // Create and store a one-time token
            $token = Str::uuid()->toString();
            Cache::put('otp_token:' . $token, $request->to, now()->addMinutes(10));
    
            return response()->json([
                'success' => true,
                'message' => 'OTP verified',
                'token'   => $token,
            ]);
        }
    
        return response()->json([
            'success' => false,
            'message' => 'OTP verification failed',
            'details' => $result,
        ], 422);
    }

}

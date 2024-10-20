<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Http\Requests\SignUpRequest; // Make sure to create this request class
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Services\WhatsAppService;
use Firebase\JWT\JWT;

class UserController extends Controller
{
    protected $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    // Sign Up Method
    public function signUp(SignUpRequest $request)
    {
        // Generate temporary token with user data
        $tempToken = JWT::encode([
            'id' => \Illuminate\Support\Str::uuid(),
            'name' => $request->name,
            'phone' => $request->phone,
            'gstNumber' => $request->gstNumber,
            'city' => $request->city,
            'exp' => time() + 600 // Valid for 10 minutes
        ], env('JWT_SECRET'));

        // Generate OTP
        $otp = rand(100000, 999999);

        // Store OTP in the otps table with token
        DB::table('otps')->insert([
            'token' => $tempToken,
            'otp' => $otp,
            'created_at' => now(),
            'expires_at' => now()->addMinutes(10), // Set expiration time
        ]);

        // Send OTP via WhatsApp
        $this->whatsappService->sendOtp($request->phone, $otp);

        return response()->json(['message' => 'OTP sent for verification!', 'token' => $tempToken], 200);
    }

    // Login Method
    public function login(LoginRequest $request)
    {
        // Find the user by phone
        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Generate temporary token with user data
        $tempToken = JWT::encode([
            'id' => $user->id,
            'phone' => $user->phone,
            'exp' => time() + 600 // Valid for 10 minutes
        ], env('JWT_SECRET'));

        // Generate OTP
        $otp = rand(100000, 999999);

        // Store OTP in the otps table with token
        DB::table('otps')->insert([
            'token' => $tempToken,
            'otp' => $otp,
            'created_at' => now(),
            'expires_at' => now()->addMinutes(10), // Set expiration time
        ]);

        // Send OTP via WhatsApp
        $this->whatsappService->sendOtp($user->phone, $otp);

        return response()->json(['message' => 'OTP sent for verification!', 'token' => $tempToken], 200);
    }

    // Verify OTP Method
    public function verifyOtp(VerifyOtpRequest $request)
    {
        // Find the OTP record based on token and OTP
        $otpRecord = DB::table('otps')
            ->where('otp', $request->otp)
            ->where('token', $request->token)
            ->where('expires_at', '>', now())  // Check if OTP is not expired
            ->first();

        if (!$otpRecord) {
            return response()->json(['message' => 'Invalid or expired OTP'], 401);
        }

        // Decode the token to retrieve user details
        $payload = JWT::decode($request->token, env('JWT_SECRET'), ['HS256']);

        // Check if it's a signup or login attempt
        $user = User::where('phone', $payload->phone)->first();

        if (!$user) {
            // User doesn't exist, create a new user for signup
            $user = User::create([
                'id' => $payload->id, // UUID from the token
                'name' => $payload->name,
                'phone' => $payload->phone,
                'gstNumber' => $payload->gstNumber,
                'city' => $payload->city,
            ]);
        }

        // Issue a new JWT token with all user data for long-term authentication
        $newJwtToken = JWT::encode([
            'id' => $user->id,
            'name' => $user->name,
            'phone' => $user->phone,
            'gstNumber' => $user->gstNumber,
            'city' => $user->city,
            'exp' => time() + (60 * 60 * 24 * 365) // Valid for 1 year
        ], env('JWT_SECRET'));

        // Clear the OTP record after successful verification
        DB::table('otps')->where('id', $otpRecord->id)->delete();

        return response()->json([
            'message' => 'OTP verified successfully!',
            'token' => $newJwtToken,
            'token_type' => 'Bearer',
            'expires_in' => 60 * 60 * 24 * 365 // 1 year in seconds
        ], 200);
    }
}

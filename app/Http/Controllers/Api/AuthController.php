<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    /**
     * @OA\Post(
     *   path="/api/auth/register",
     *   operationId="registerUser",
     *   tags={"Authentication"},
     *   summary="Register a new user",
     *   description="Register a new user.",
     *   @OA\RequestBody(
     *     required=true,
     *     description="User data",
     *     @OA\JsonContent(
     *       required={"name","email","password"},
     *       @OA\Property(property="name", type="string", example="John Doe", description="User name"),
     *       @OA\Property(property="email", type="email", example="john.doe@email.com"),
     *       @OA\Property(property="password", type="string", example="password"),
     *     ),
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="User created successfully or email already exists",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="An email has been sent to your email address. Please verify."),
     *     ),
     *   ),
     * )
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email',
            'password' => 'required|string'
        ]);

        if (!User::where('email', $request->email)->exists()) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            event(new Registered($user));
        } else {
            Log::info(
                'Someone tried to register with an existing email address. ' .
                    "E-Mail: " . $request->email .
                    " IP: " . $request->ip()
            );
        }


        return response()->json([
            'message' => 'An email has been sent to your email address. Please verify.',
        ], 201);
    }

    /**
     * @OA\Post(
     *   path="/api/auth/login",
     *   operationId="loginUser",
     *   tags={"Authentication"},
     *   summary="Login user",
     *   description="Login user.",
     *   security={{"bearerAuth":{}}},
     *   @OA\RequestBody(
     *     required=true,
     *     description="User credentials",
     *     @OA\JsonContent(
     *       required={"email","password"},
     *       @OA\Property(property="email", type="email", example="john.doe@email.com"),
     *       @OA\Property(property="password", type="string", example="password"),
     *     ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="User logged in successfully",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="User logged in successfully"),
     *       @OA\Property(property="user", type="object"),
     *       @OA\Property(property="authorization", type="object",
     *         @OA\Property(property="type", type="string", example="Bearer"),
     *         @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9"),
     *       ),
     *     ),
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthorized",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Unauthorized"),
     *     ),
     *   ),
     * )
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only(['email', 'password']);
        $token = Auth::attempt($credentials);

        if (!$token) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        $user = Auth::user();

        if (!$user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        return response()->json([
            'message' => 'User logged in successfully',
            'user' => $user,
            'authorization' => [
                'type' => 'Bearer',
                'token' => $token,
            ],
        ], 200);
    }

    /**
     * @OA\Post(
     *   path="/api/auth/refresh",
     *   operationId="refreshToken",
     *   tags={"Authentication"},
     *   summary="Refresh token",
     *   description="Refresh token.",
     *   security={{"bearerAuth":{}}},
     *   @OA\RequestBody(
     *     required=true,
     *     description="Refresh token",
     *     @OA\JsonContent(
     *       required={"type", "token"},
     *       @OA\Property(property="type", type="string", example="Bearer"),
     *       @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9"),
     *     ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Token refreshed successfully",
     *     @OA\JsonContent(
     *       @OA\Property(property="user", type="object"),
     *       @OA\Property(property="authorization", type="object",
     *         @OA\Property(property="type", type="string", example="Bearer"),
     *         @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9"),
     *       ),
     *     ),
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthorized",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Unauthorized"),
     *     ),
     *   ),
     * )
     */
    public function refresh()
    {
        return response()->json([
            'user' => Auth::user(),
            'authorization' => [
                'type' => 'Bearer',
                'token' => Auth::refresh(),
            ],
        ]);
    }

    /**
     * @OA\Post(
     *   path="/api/auth/logout",
     *   operationId="logoutUser",
     *   tags={"Authentication"},
     *   summary="Logout user",
     *   description="Logout user.",
     *   security={{"bearerAuth":{}}},
     *   @OA\RequestBody(
     *     required=true,
     *     description="Delete token.",
     *     @OA\JsonContent(
     *       required={"type", "token"},
     *       @OA\Property(property="type", type="string", example="Bearer"),
     *       @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9"),
     *     ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successfully logged out",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Successfully logged out"),
     *     ),
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthorized",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Unauthorized"),
     *     ),
     *   ),
     * )
     */
    public function logout()
    {
        Auth::logout();
        return response()->json([
            'message' => 'Successfully logged out',
        ]);
    }
}

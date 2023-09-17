<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/email/verify/{id}",
     *     summary="Verify email",
     *     tags={"Email Verification"},
     *     description="Verifies the user email if the email is not verified yet.",
     *     operationId="verify",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User id",
     *         required=true,
     *     ),
     *     @OA\Response(
     *         response=302,
     *         description="Redirects to the API documentation page.",
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid/Expired url provided.",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Invalid/Expired url provided."
     *             )
     *         )
     *     )
     * )
     */
    public function verify($user_id, Request $request)
    {
        if (!$request->hasValidSignature()) {
            return response()->json(["message" => "Invalid/Expired url provided."], 401);
        }

        $user = User::findOrFail($user_id);

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        return redirect()->to('/api/documentation');
    }

    /**
     * @OA\Get(
     *     path="/api/email/resend",
     *     summary="Resend verification email",
     *     tags={"Email Verification"},
     *     description="Resends the verification email if the email is not verified yet.",
     *     operationId="resend",
     *     @OA\Response(
     *         response=200,
     *         description="Email verification link sent to the user email address.",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Email verification link sent to the user email address."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Email already verified.",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Email already verified."
     *             )
     *         )
     *     )
     * )
     */

    public function resend()
    {
        if (auth()->user()->hasVerifiedEmail()) {
            return response()->json(["message" => "Email already verified."], 400);
        }

        auth()->user()->sendEmailVerificationNotification();

        return response()->json(["message" => "Email verification link sent to your email address."], 200);
    }
}

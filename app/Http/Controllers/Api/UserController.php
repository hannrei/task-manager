<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->authorizeResource(User::class, 'user');
    }

    /**
     * @OA\Get(
     *     path="/api/users",
     *     summary="Get all users",
     *     description="Returns a list of all users",
     *     tags={"Users"},
     *     operationId="getUsers",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/UserResource")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *     ),
     * )
     */
    public function index()
    {
        return UserResource::collection(User::get());
    }

    public function store(Request $request)
    {
        //
    }

    /**
     * @OA\Get(
     *     path="/api/users/{user}",
     *     summary="Get a user by ID",
     *     description="Returns a single user",
     *     operationId="getUserById",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         description="ID of user to return",
     *         in="path",
     *         name="user",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             format="uuid"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/UserResource")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     ),
     * )
     */
    public function show(User $user)
    {
        return new UserResource($user);
    }

    /**
     * @OA\Put(
     *     path="/api/users/{user}",
     *     summary="Update a user",
     *     description="Update a user by ID",
     *     operationId="updateUser",
     *     security={{"bearerAuth":{}}},
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         description="ID of user to update",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             format="uuid"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="User data to update",
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Jane Doe"),
     *             @OA\Property(property="email", type="string", example="jane.doe@email.com"),
     *             @OA\Property(property="password", type="string", example="password"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/UserResource")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="This action is unauthorized."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="The given data was invalid",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="The given data was invalid."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="User not found."
     *             )
     *         )
     *     ),
     * )
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email',
            'password' => 'sometimes|string|min:8',
        ]);

        if (User::where('email', $request->email)->exists()) {
            return response()->json([
                'message' => 'The given data was invalid.',
            ], 422);
        }

        $oldEmail = $user->email;

        $user->update([
            'name' => $request->name ?? $user->name,
            'email' => $request->email ?? $user->email,
            'password' => $request->password ?? $user->password,
        ]);

        $message = 'User updated successfully.';

        if ($oldEmail !== $user->email) {
            $user->email_verified_at = null;
            $user->old_email = $oldEmail;
            $user->save();
            $user->sendEmailVerificationNotification();
            $message = 'User updated successfully. A new verification email has been sent.';
        }

        return response()->json(
            [
                'message' => $message,
                'user' => new UserResource($user),
            ],
            200
        );
    }


    /**
     * @OA\Delete(
     *     path="/api/users/{user}",
     *     summary="Delete a user",
     *     description="Delete a user by ID",
     *     operationId="deleteUser",
     *     security={{"bearerAuth":{}}},
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         description="ID of user to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             format="uuid"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="User deleted successfully."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Unauthenticated."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="This action is unauthorized."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     )
     * )
     */
    public function destroy(User $user)
    {
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully.',
        ], 200);
    }
}

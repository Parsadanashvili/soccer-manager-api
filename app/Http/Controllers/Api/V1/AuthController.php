<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\GenerateTeam;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Requests\Api\V1\Auth\RegisterRequest;
use App\Http\Resources\Api\V1\TeamResource;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{

    public function register(RegisterRequest $request, GenerateTeam $generateTeam): JsonResponse
    {
        $user = DB::transaction(function () use ($request, $generateTeam): User {
            $user = User::create($request->validated());
            $generateTeam->handle($user);

            return $user;
        });

        return response()->json([
            'token' => $user->createToken('api')->plainTextToken,
            'user' => UserResource::make($user),
            'team' => TeamResource::make($user->team),
        ], 201);
    }


    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->validated('email'))->first();

        if (! $user || ! Hash::check($request->validated('password'), $user->password)) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        return response()->json([
            'token' => $user->createToken('api')->plainTextToken,
            'user' => UserResource::make($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => __('auth.logged_out'),
        ]);
    }
}

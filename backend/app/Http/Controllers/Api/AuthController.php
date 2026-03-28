<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'clinician',
        ]);
        $user->assignRole('clinician');

        $abilities = $this->abilitiesForRole('clinician');
        $token = $user->createToken('auth_token', $abilities)->plainTextToken;

        return response()->json([
            'success' => true,
            'data' => [
                'user' => new UserResource($user),
                'token' => $token,
            ],
        ]);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        if (! Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']])) {
            return response()->json(['success' => false, 'message' => 'Invalid credentials'], 401);
        }

        /** @var User $user */
        $user = User::where('email', $credentials['email'])->firstOrFail();

        $role = $user->hasRole('admin') ? 'admin' : ($user->hasRole('editor') ? 'editor' : 'clinician');
        $abilities = $this->abilitiesForRole($role);
        $token = $user->createToken('auth_token', $abilities)->plainTextToken;

        return response()->json([
            'success' => true,
            'data' => [
                'user' => new UserResource($user),
                'token' => $token,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }
        return response()->json(['success' => true, 'message' => 'Logged out']);
    }

    private function abilitiesForRole(string $role): array
    {
        return match ($role) {
            'admin' => ['admin:*'],
            'editor' => ['content:*'],
            default => ['basic'],
        };
    }
}

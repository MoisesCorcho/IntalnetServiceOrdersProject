<?php

namespace App\Services;

use App\Http\Resources\UserResource;
use App\Interfaces\TodoRepositoryInterface;
use App\Models\Todo;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Exception;

class AuthService
{
    public function registerUser(array $data): array
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $token = $user->createToken($data['device_name'])->plainTextToken;

        return [
            'user' => new UserResource($user),
            'token' => $token
        ];
    }

    public function loginUser(array $data): array
    {
        if (!Auth::attempt([
            'email' => $data['email'],
            'password' => $data['password']
        ])) {
            throw new Exception('Invalid credentials');
        }

        $user = User::where('email', $data['email'])->firstOrFail();
        $token = $user->createToken($data['device_name'])->plainTextToken;

        return [
            'user' => new UserResource($user),
            'token' => $token
        ];
    }

    public function logoutUser(User $user): void
    {
        $user->currentAccessToken()->delete();
    }
}

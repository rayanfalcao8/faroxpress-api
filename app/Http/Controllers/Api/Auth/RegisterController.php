<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function __invoke(RegisterRequest $request)
    {
        $user = User::query()->create([
            'name' => (string) $request->input('name'),
            'email' => (string) $request->input('email'),
            'password' => Hash::make((string) $request->input('password')),
        ]);

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'user' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email],
            'token' => $token,
        ], 201);
    }
}

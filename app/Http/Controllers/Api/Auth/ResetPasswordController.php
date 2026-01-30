<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class ResetPasswordController extends Controller
{
    public function __invoke(ResetPasswordRequest $request)
    {
        $status = Password::reset(
            [
                'email' => (string) $request->input('email'),
                'password' => (string) $request->input('password'),
                'password_confirmation' => (string) $request->input('password_confirmation'),
                'token' => (string) $request->input('token'),
            ],
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make((string) $request->input('password')),
                ])->save();

                $user->tokens()->delete();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return response()->json(['ok' => false, 'message' => __($status)], 422);
        }

        return response()->json(['ok' => true, 'status' => __($status)]);
    }
}

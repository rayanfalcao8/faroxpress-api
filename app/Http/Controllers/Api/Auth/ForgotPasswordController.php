<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    public function __invoke(ForgotPasswordRequest $request)
    {
        $status = Password::sendResetLink([
            'email' => (string) $request->input('email'),
        ]);

        return response()->json(['ok' => true, 'status' => __($status)]);
    }
}

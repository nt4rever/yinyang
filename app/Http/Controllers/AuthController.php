<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\VerifyEmailRequest;
use App\Services\AuthService;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}

    public function verifyEmail(VerifyEmailRequest $request)
    {
        if (! $request->hasValidSignature()) {
            abort(403, trans('Invalid signature.'));
        }

        $this->authService->verifyEmail($request->id);

        return redirect()->to('/');
    }
}

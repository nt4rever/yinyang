<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\VerifyEmailRequest;
use App\Services\AuthService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}

    public function verifyEmail(VerifyEmailRequest $request)
    {
        try {
            if (! $request->hasValidSignature()) {
                throw new BadRequestHttpException(trans('Invalid signature.'));
            }

            $this->authService->verifyEmail($request->id);

            return redirect()->to('/');
        } catch (\Throwable $th) {
            return view('laravel-exceptions::403', ['exception' => $th]);
        }
    }
}

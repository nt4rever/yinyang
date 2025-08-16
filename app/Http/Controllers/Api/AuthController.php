<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\VerifyEmailRequest;
use App\Http\Resources\User\UserResource;
use App\Models\PersonalAccessToken;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}

    public function login(LoginRequest $request)
    {
        $result = $this->authService->login(
            $request->email,
            $request->password
        );

        return response()->json($result);
    }

    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken();

        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        } else {
            auth('web')->logout();
        }

        return response()->noContent();
    }

    public function profile(Request $request)
    {
        return new UserResource($request->user());
    }

    public function verifyEmail(VerifyEmailRequest $request)
    {
        try {
            if (! $request->hasValidSignature()) {
                throw new BadRequestHttpException(trans('Invalid signature.'));
            }

            $this->authService->verifyEmail($request->id);

            // Redirect to login page with success message
            return redirect()->to('/?success=true');
        } catch (\Throwable $th) {
            // Redirect to login page with error message
            return redirect()->to('/?error=true');
        }
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\KeycloakBackchannelLogoutRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\User\UserResource;
use App\Models\PersonalAccessToken;
use App\Services\AuthService;
use App\Services\KeycloakLogoutTokenVerifier;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

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

    public function redirectToKeycloak()
    {
        return response()->json([
            'redirect_url' => Socialite::driver('keycloak')->stateless()->redirect()->getTargetUrl(),
        ]);
    }

    public function handleKeycloakCallback()
    {
        return response()->json(
            $this->authService->loginWithKeycloak(
                Socialite::driver('keycloak')->stateless()->user()
            )
        );
    }

    public function backchannelLogout(
        KeycloakBackchannelLogoutRequest $request,
        KeycloakLogoutTokenVerifier $logoutTokenVerifier
    ): Response {
        $payload = $logoutTokenVerifier->verify($request->logout_token);

        PersonalAccessToken::query()
            ->where('keycloak_session_id', $payload['sid'])
            ->delete();

        return response()->noContent();
    }

    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken();

        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        } else {
            Auth::logout();
        }

        return response()->noContent();
    }

    public function profile(Request $request)
    {
        return new UserResource($request->user());
    }
}

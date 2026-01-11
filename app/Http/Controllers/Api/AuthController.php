<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\User\UserResource;
use App\Models\PersonalAccessToken;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}

    public function login(LoginRequest $request)
    {
        $user = $this->authService->login(
            $request->email,
            $request->password
        );

        Auth::login($user);

        $request->session()->regenerate();
        $request->session()->regenerateToken();
        $request->session()->put('tenant_id', $user->tenants()->value('tenants.id'));

        return (new UserResource($request->user()))->setTenant(current_tenant());
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
        return (new UserResource($request->user()))->setTenant(current_tenant());
    }
}

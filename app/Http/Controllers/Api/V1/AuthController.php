<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CustomerResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $request->merge(['email' => mb_strtolower(trim((string) $request->input('email')))]);

        $attributes = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:10', 'confirmed'],
        ]);

        $user = User::query()->create($attributes);

        return $this->customerResponse($request, $user, 201);
    }

    public function login(Request $request): JsonResponse
    {
        $request->merge(['email' => mb_strtolower(trim((string) $request->input('email')))]);

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
        ]);

        $user = User::query()->where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages(['email' => [__('auth.failed')]]);
        }

        return $this->customerResponse($request, $user, 200, $credentials['remember'] ?? false);
    }

    public function me(Request $request): CustomerResource
    {
        return CustomerResource::make($request->user());
    }

    public function logout(Request $request): Response
    {
        $user = $request->user();
        $currentToken = $user->currentAccessToken();

        if ($currentToken instanceof PersonalAccessToken) {
            $currentToken->delete();
        }

        if ($request->hasSession()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->noContent();
    }

    private function customerResponse(Request $request, User $user, int $status = 200, bool $remember = false): JsonResponse
    {
        $resource = CustomerResource::make($user);

        if ($request->hasSession()) {
            $this->startSessionIfAvailable($request, $user, $remember);
        }

        $resource->additional(['token' => $user->createToken('kora-mode-web')->plainTextToken]);

        return $resource->response()->setStatusCode($status);
    }

    private function startSessionIfAvailable(Request $request, User $user, bool $remember = false): void
    {
        if (! $request->hasSession()) {
            return;
        }

        Auth::guard('web')->login($user, $remember);
        $request->session()->regenerate();
    }
}

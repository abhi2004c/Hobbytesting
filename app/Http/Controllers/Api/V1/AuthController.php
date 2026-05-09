<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Auth\DTOs\LoginDTO;
use App\Domain\Auth\DTOs\RegisterDTO;
use App\Domain\Auth\Exceptions\AccountSuspendedException;
use App\Domain\Auth\Exceptions\InvalidCredentialsException;
use App\Domain\Auth\Services\AuthService;
use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends BaseApiController
{
    public function __construct(private readonly AuthService $auth) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->auth->register(RegisterDTO::fromRequest($request->validated()));

        $token = $user->createToken('mobile', ['*'], now()->addDays(30))->plainTextToken;

        return $this->successResponse([
            'user'  => $user->only(['id', 'name', 'email', 'avatar_url']),
            'token' => $token,
        ], 'Registration successful', 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $result = $this->auth->login(LoginDTO::fromRequest(
                $request->validated() + ['ip_address' => $request->ip()],
            ));
        } catch (InvalidCredentialsException $e) {
            return $this->errorResponse($e->getMessage(), 401);
        } catch (AccountSuspendedException $e) {
            return $this->errorResponse($e->getMessage(), 403);
        }

        return $this->successResponse([
            'user'       => $result['user']->only(['id', 'name', 'email', 'avatar_url']),
            'token'      => $result['token'],
            'expires_at' => $result['expires_at']->toIso8601String(),
        ], 'Login successful');
    }

    public function logout(Request $request): JsonResponse
    {
        $this->auth->logout($request->user());

        return $this->successResponse(null, 'Logged out');
    }

    public function me(Request $request): JsonResponse
    {
        return $this->successResponse(
            $request->user()->load(['interests', 'roles']),
            'Current user',
        );
    }
}
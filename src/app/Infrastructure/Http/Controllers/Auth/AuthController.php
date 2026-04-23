<?php

namespace App\Infrastructure\Http\Controllers\Auth;

use App\Application\Auth\AuthenticateUser\AuthenticateUserDTO;
use App\Application\Auth\AuthenticateUser\AuthenticateUserUseCase;
use App\Application\Auth\RegisterUser\RegisterUserDTO;
use App\Application\Auth\RegisterUser\RegisterUserUseCase;
use App\Application\Auth\RevokeToken\RevokeTokenUseCase;
use App\Infrastructure\Http\Requests\Auth\LoginRequest;
use App\Infrastructure\Http\Requests\Auth\RegisterUserRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AuthController extends Controller
{
    public function __construct(
        private RegisterUserUseCase     $register,
        private AuthenticateUserUseCase $authenticate,
        private RevokeTokenUseCase      $revoke,
    ) {}

    public function register(RegisterUserRequest $request): JsonResponse
    {
        $result = $this->register->execute(new RegisterUserDTO(
            name:     $request->input('name'),
            email:    $request->input('email'),
            password: $request->input('password'),
        ));

        return response()->json([
            'data'  => ['name' => $result->name, 'email' => $result->email, 'role' => $result->role],
            'token' => $result->token,
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authenticate->execute(new AuthenticateUserDTO(
            email:    $request->input('email'),
            password: $request->input('password'),
        ));

        return response()->json([
            'data'  => ['name' => $result->name, 'email' => $result->email, 'role' => $result->role],
            'token' => $result->token,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $this->revoke->execute($request->user()->id);

        return response()->json(['message' => 'Logout realizado com sucesso.']);
    }
}

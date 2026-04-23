<?php

namespace App\Infrastructure\Http\Controllers\Auth;

use App\Application\Auth\UpdateUserRole\UpdateUserRoleDTO;
use App\Application\Auth\UpdateUserRole\UpdateUserRoleUseCase;
use App\Domain\User\User;
use App\Infrastructure\Http\Requests\Auth\UpdateUserRoleRequest;
use App\Infrastructure\Http\Resources\Auth\UserResource;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    public function __construct(
        private UpdateUserRoleUseCase $updateRole,
    ) {}

    public function updateRole(UpdateUserRoleRequest $request, int $id): UserResource
    {
        Gate::authorize('updateRole', User::class);

        $user = $this->updateRole->execute(new UpdateUserRoleDTO(
            userId: $id,
            role:   $request->input('role'),
        ));

        return new UserResource($user);
    }
}

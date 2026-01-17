<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\RoleRepositoryInterface;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Eloquent\Collection;
use App\Models\User;

class RoleRepository implements RoleRepositoryInterface
{
	public function getInternalRoles(): Collection
	{
		return Role::withCount('users')->get();
	}

	public function getInternalRolesPaginated(int $perPage = 10)
	{
		return Role::withCount('users')->paginate($perPage);
	}

	public function findById(int $id): ?Role
	{
		return Role::find($id);
	}

	public function syncUsers(Role $role, array $userIds): void
	{
		// Safer approach using Spatie's sync:
		$role->users()->sync($userIds);
	}
}

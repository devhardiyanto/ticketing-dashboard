<?php

namespace App\Services;

use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Exception;

class RoleService
{
    public function __construct(
        protected RoleRepositoryInterface $roleRepository,
        protected UserRepositoryInterface $userRepository
    ) {}

    /**
     * Get internal roles.
     */
    public function getInternalRoles(): Collection
    {
        return $this->roleRepository->getInternalRoles();
    }

    public function getInternalRolesPaginated(int $perPage = 10, array $columns = ['*'])
    {
        return $this->roleRepository->getInternalRolesPaginated($perPage, $columns);
    }

    public function getRole(int $id): ?Role
    {
        return $this->roleRepository->findById($id);
    }

    /**
     * Update users for a role.
     */
    public function updateUsers(int $roleId, array $userIds): void
    {
        DB::beginTransaction();

        try {
            $role = $this->roleRepository->findById($roleId);

            if (!$role) {
                throw new Exception("Role not found.");
            }

            $this->roleRepository->syncUsers($role, $userIds);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}

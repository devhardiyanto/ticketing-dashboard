<?php

namespace App\Repositories\Contracts;

use Spatie\Permission\Models\Role;
use Illuminate\Database\Eloquent\Collection;

interface RoleRepositoryInterface
{
    /**
     * Get all internal roles (Super Admin & Platform Staff).
     */
    public function getInternalRoles(): Collection;

    /**
     * Get paginated internal roles.
     */
    public function getInternalRolesPaginated(int $perPage = 10);

    /**
     * Find a role by ID.
     */
    public function findById(int $id): ?Role;

    /**
     * Sync users to a specific role.
     *
     * @param Role $role
     * @param array $userIds
     * @return void
     */
    public function syncUsers(Role $role, array $userIds): void;
}

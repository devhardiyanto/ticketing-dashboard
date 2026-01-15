<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\OrganizationRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    protected $user_repo;

    protected $org_repo;

    public function __construct(
        UserRepositoryInterface $user_repo,
        OrganizationRepositoryInterface $org_repo
    ) {
        $this->user_repo = $user_repo;
        $this->org_repo = $org_repo;
    }

    public function index(Request $request)
    {
        $params = $request->only(['search', 'limit', 'page', 'organization_id', 'role_id', 'status']);
        $params['exclude_id'] = auth()->id();

        $users = $this->user_repo->getAll($params);

        // Map roles for specific user serialization if needed, but 'with' in repo helps.
        // Frontend expects 'role' object. Spatie provides 'roles' array.
        // We might need to transform the collection to mapped structure if frontend expects single role.
        // Assuming single role per user for now based on UI 'Form Select Role'.

        $organizations = $this->org_repo->all()->map(function ($org) {
            return [
                'id' => $org->id,
                'name' => $org->name,
            ];
        });

        $roles = Role::with('permissions')->get()->map(function ($role) {
            return [
                'id' => $role->id,
                'display_name' => $role->display_name ?? $role->name,
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name'),
            ];
        });

        $available_permissions = Permission::all()->map(function ($p) {
            return ['id' => $p->id, 'name' => $p->name];
        });

        $organization_model = null;
        if (isset($params['organization_id'])) {
            $organization_model = $this->org_repo->find($params['organization_id']);
        }

        $routeName = $request->route()->getName();

        if (str_contains($routeName ?? '', 'organization.user')) {
            return Inertia::render('organization/OrganizationUserIndex', [
                'users' => $users,
                'organizations' => $organizations,
                'roles' => $roles,
                'availablePermissions' => $available_permissions,
                'organization_model' => $organization_model,
                'filters' => $params,
            ]);
        }

        return Inertia::render('user/UserIndex', [
            'users' => $users,
            'organizations' => $organizations,
            'roles' => $roles,
            'availablePermissions' => $available_permissions,
            'filters' => $params,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:pgsql.users',
            // 'password' => 'required|string|min:8', // Generated automatically
            'organization_id' => 'nullable|string|exists:core_pgsql.organizations,id',
            'role_id' => 'nullable|integer|exists:pgsql.roles,id',
            'phone_number' => 'nullable|string|max:20',
            'status' => 'required|string|in:active,inactive',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        // Generate random password
        $rawPassword = \Illuminate\Support\Str::random(10);
        $data['password'] = Hash::make($rawPassword);

        $user = $this->user_repo->create(Arr::except($data, ['role_id', 'permissions']));

        if (! empty($data['role_id'])) {
            $role = Role::findById($data['role_id']); // Spatie method
            $user->assignRole($role);
        }

        if (! empty($data['permissions'])) {
            $user->syncPermissions($data['permissions']);
        }

        // Send email
        try {
            \Illuminate\Support\Facades\Mail::to($user)->send(new \App\Mail\UserCreated($user->email, $rawPassword));
        } catch (\Exception $e) {
            // Log error but don't fail the request
            \Illuminate\Support\Facades\Log::error('Failed to send user creation email: '.$e->getMessage());
        }

        return redirect()->back()->with('success', 'User created. Password sent to email.');
    }

    public function update(Request $request, string $id)
    {
        $user = $this->user_repo->find($id);
        if (! $user) {
            abort(404, 'User not found');
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('pgsql.users')->ignore($user->id)],
            // Allow empty password (no change)
            'password' => 'nullable|string|min:8',
            'organization_id' => 'nullable|string|exists:core_pgsql.organizations,id',
            'role_id' => 'nullable|integer|exists:pgsql.roles,id',
            'phone_number' => 'nullable|string|max:20',
            'status' => 'required|string|in:active,inactive',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        if ($user->organization_id && isset($data['organization_id']) && $data['organization_id'] !== $user->organization_id) {
            unset($data['organization_id']);
        }

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $this->user_repo->update($id, Arr::except($data, ['role_id', 'permissions']));

        // Refresh model to handle relations
        $user->refresh();

        if (! empty($data['role_id'])) {
            $user->syncRoles([$data['role_id']]);
        } else {
            // If role_id is explicit null/empty but present in request?
            // If frontend sends null, we remove roles?
            // Usually default to no change or remove. Assuming 'sync' means set to this.
            if (array_key_exists('role_id', $data)) {
                $user->syncRoles([]);
            }
        }

        if (array_key_exists('permissions', $data)) {
            $user->syncPermissions($data['permissions']);
        }

        return redirect()->back();
    }

    public function destroy(string $id)
    {
        $this->user_repo->delete($id);

        return redirect()->back();
    }

    public function toggleStatus(string $id)
    {
        $user = $this->user_repo->find($id);
        if (! $user) {
            abort(404, 'User not found');
        }

        $newStatus = $user->status === 'active' ? 'inactive' : 'active';
        $this->user_repo->update($id, ['status' => $newStatus]);

        return redirect()->back();
    }
}

<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\OrganizationRepositoryInterface;
use App\Models\Dashboard\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

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

		$users = $this->user_repo->getAll($params);

		$organizations = $this->org_repo->all()->map(function ($org) {
			return [
				'id' => $org->id,
				'name' => $org->name,
			];
		});

		$roles = Role::all()->map(function ($role) {
			return [
				'id' => $role->id,
				'display_name' => $role->display_name,
			];
		});

		// If scoped by organization (Menu Organization), fetch the org model context
		$organization_model = null;
		if (isset($params['organization_id'])) {
			$organization_model = $this->org_repo->find($params['organization_id']);
		}

		// Determine view based on context?
		// Actually, we can share the same controller method but render different Inertia pages
		// based on the route or request.
		// Or simpler: The Frontend decides which "Page Component" to use if we just return data.
		// But here we must return Inertia::render('PageComponent').
		// User requested distinct menus.

		// If the request comes from the "Organization Scoped" route (e.g. via 'organization/..' prefix check or param)
		// But cleaner way: use distinct route definitions calling same controller but passing a 'view' hint?
		// No, standard Inertia way is distinct Controller method or logic.

		// Let's assume for now we use one 'user/UserIndex' for standalone.
		// And maybe we return 'organization/OrganizationUserIndex' if explicit 'scoped' param is passed?
		// User requested: Menu Users (Standalone) and Menu Organization.

		// Let's check the route name to decide?
		$routeName = $request->route()->getName();

		if (str_contains($routeName ?? '', 'organization.user')) {
			return Inertia::render('organization/OrganizationUserIndex', [
				'users' => $users,
				'organizations' => $organizations, // For the top combobox
				'roles' => $roles,
				'organization_model' => $organization_model, // The selected org context
				'filters' => $params,
			]);
		}

		return Inertia::render('user/UserIndex', [
			'users' => $users,
			'organizations' => $organizations,
			'roles' => $roles,
			'filters' => $params,
		]);
	}

	public function store(Request $request)
	{
		$data = $request->validate([
			'name' => 'required|string|max:255',
			'email' => 'required|string|email|max:255|unique:pgsql.users',
			'password' => 'required|string|min:8',
			'organization_id' => 'nullable|string|exists:core_pgsql.organizations,id',
			'role_id' => 'nullable|integer|exists:pgsql.roles,id',
			'phone_number' => 'nullable|string|max:20',
			'status' => 'required|string|in:active,inactive',
		]);

		$data['password'] = Hash::make($data['password']);

		$this->user_repo->create($data);

		return redirect()->back();
	}

	public function update(Request $request, string $id)
	{
		$user = $this->user_repo->find($id);
		if (!$user) {
			abort(404, 'User not found');
		}

		$data = $request->validate([
			'name' => 'required|string|max:255',
			'email' => ['required', 'string', 'email', 'max:255', Rule::unique('pgsql.users')->ignore($user->id)],
			'password' => 'nullable|string|min:8', // Nullable on update
			'organization_id' => 'nullable|string|exists:core_pgsql.organizations,id',
			'role_id' => 'nullable|integer|exists:pgsql.roles,id',
			'phone_number' => 'nullable|string|max:20',
			'status' => 'required|string|in:active,inactive',
		]);

		// Logic: If user already has organization_id, it cannot be changed.
		if ($user->organization_id && isset($data['organization_id']) && $data['organization_id'] !== $user->organization_id) {
			// Ignore the change attempt (or could throw error)
			unset($data['organization_id']);
		}

		if (!empty($data['password'])) {
			$data['password'] = Hash::make($data['password']);
		} else {
			unset($data['password']);
		}

		$this->user_repo->update($id, $data);

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
		if (!$user) {
			abort(404, 'User not found');
		}

		$newStatus = $user->status === 'active' ? 'inactive' : 'active';
		$this->user_repo->update($id, ['status' => $newStatus]);

		return redirect()->back();
	}
}

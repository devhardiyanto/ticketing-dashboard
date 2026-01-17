<?php

namespace App\Http\Controllers;

use App\Services\RoleService;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;

class RoleController extends Controller
{
	public function __construct(
		protected RoleService $roleService,
		protected UserRepositoryInterface $userRepository
	) {
	}

	public function data(Request $request)
	{
		return $this->roleService->getInternalRolesPaginated($request->get('limit', 10));
	}

	public function index()
	{
		$roles = $this->roleService->getInternalRoles();

		$users = $this->userRepository->all();

		$usersMinimal = $users->map(function ($user) {
			return [
				'id' => $user->id,
				'name' => $user->name,
				'email' => $user->email,
			];
		});

		$roles->load('users:id');

		return Inertia::render('role/RoleIndex', [
			'roles' => $roles,
			'users' => $usersMinimal, // Candidate users
		]);
	}

	public function update(Request $request, int $id)
	{
		$request->validate([
			'user_ids' => 'present|array',
			'user_ids.*' => 'exists:users,id',
		]);

		$this->roleService->updateUsers($id, $request->user_ids);

		return Redirect::route('role.index')
			->with('success', 'Role assignments updated successfully.');
	}
}

<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\OrganizationRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Dashboard\User;
use Illuminate\Validation\Rules;

class OrganizationController extends Controller
{
	protected $organization_repo;

	public function __construct(OrganizationRepositoryInterface $organization_repo)
	{
		$this->organization_repo = $organization_repo;
	}

	public function index(Request $request)
	{
		return Inertia::render('organization/OrganizationIndex');
	}

	public function data(Request $request)
	{
		$params = $request->only(['search', 'limit', 'page']);
		$columns = ['id', 'name', 'business_type', 'email', 'phone_number', 'status', 'created_at'];

		$organizations = $this->organization_repo->getAll($params, $columns);

		return response()->json($organizations);
	}

	public function store(Request $request)
	{
		$validated = $request->validate([
			// Organization details
			'name' => 'required|string|max:255',
			'business_type' => 'required|string|max:255',
			'email' => 'required|email|max:255',
			'phone_number' => 'nullable|string|max:20',
			'address' => 'nullable|string',
			'tax_id' => 'nullable|string|max:50',

			// Initial Admin User details
			'admin_name' => 'required|string|max:255',
			'admin_email' => 'required|string|email|max:255|unique:users,email',
			'password' => ['required', 'confirmed', Rules\Password::defaults()],
		]);

		DB::transaction(function () use ($validated) {
			// 1. Create Organization
			$orgData = [
				'name' => $validated['name'],
				'business_type' => $validated['business_type'],
				'email' => $validated['email'],
				'phone_number' => $validated['phone_number'],
				'address' => $validated['address'],
				'tax_id' => $validated['tax_id'],
				'status' => 'active', // Default status
			];
			$organization = $this->organization_repo->create($orgData);

			// 2. Create Admin User linked to Organization
			$user = User::create([
				'name' => $validated['admin_name'],
				'email' => $validated['admin_email'],
				'phone_number' => $validated['phone_number'],
				'password' => Hash::make($validated['password']),
				'organization_id' => $organization->id,
				'status' => 'active',
			]);

			// 3. Assign specific organization admin role
			$user->assignRole('org_admin');
		});

		return redirect()->back()->with('success', 'Organization and Admin User created successfully.');
	}

	public function show(string $id)
	{
		$organization = $this->organization_repo->find($id);

		if (!$organization) {
			abort(404, 'Organization not found');
		}

		return response()->json($organization);
	}

	public function update(Request $request, string $id)
	{
		$validated = $request->validate([
			'name' => 'required|string|max:255',
			'business_type' => 'required|string|max:255',
			'email' => 'required|email|max:255',
			'phone_number' => 'nullable|string|max:20',
			'address' => 'nullable|string',
			'tax_id' => 'nullable|string|max:50',
			'status' => 'required|in:active,inactive',
		]);

		$this->organization_repo->update($id, $validated);

		return redirect()->back()->with('success', 'Organization updated successfully.');
	}

	public function destroy(string $id)
	{
		$this->organization_repo->delete($id);

		return redirect()->back()->with('success', 'Organization deleted successfully.');
	}
}

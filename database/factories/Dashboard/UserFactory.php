<?php

namespace Database\Factories\Dashboard;

use App\Models\Core\Organization;
use App\Models\Dashboard\Role;
use App\Models\Dashboard\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Dashboard\User>
 */
class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= 'password',
            'phone_number' => fake()->phoneNumber(),
            'role_id' => null, // Will be set by states
            'organization_id' => null, // Will be set by states
            'status' => 'active',
            'last_login_at' => fake()->dateTimeBetween('-1 month', 'now'),
            'remember_token' => Str::random(10),
            //   'two_factor_secret' => null,
            //   'two_factor_recovery_codes' => null,
            //   'two_factor_confirmed_at' => null,
        ];
    }

    /**
     * Indicate that the user is a super admin.
     */
    public function superAdmin(): static
    {
        return $this->state(function (array $attributes) {
            $role = Role::where('name', 'super_admin')->first();
            return [
                'role_id' => $role?->id,
                'organization_id' => null, // Super admin doesn't belong to any org
            ];
        });
    }

    /**
     * Indicate that the user is platform staff (internal team).
     */
    public function platformStaff(): static
    {
        return $this->state(function (array $attributes) {
            $role = Role::where('name', 'platform_staff')->first();
            return [
                'role_id' => $role?->id,
                'organization_id' => null, // Platform staff doesn't belong to any org
            ];
        });
    }

    /**
     * Indicate that the user is an organization admin.
     */
    public function orgAdmin(?Organization $organization = null): static
    {
        return $this->state(function (array $attributes) use ($organization) {
            $role = Role::where('name', 'org_admin')->first();

            if (!$organization) {
                throw new \InvalidArgumentException('Organization is required for org_admin users');
            }

            return [
                'role_id' => $role?->id,
                'organization_id' => $organization->id,
            ];
        });
    }

    /**
     * Indicate that the user is organization staff.
     */
    public function orgStaff(?Organization $organization = null): static
    {
        return $this->state(function (array $attributes) use ($organization) {
            $role = Role::where('name', 'org_staff')->first();

            if (!$organization) {
                throw new \InvalidArgumentException('Organization is required for org_staff users');
            }

            return [
                'role_id' => $role?->id,
                'organization_id' => $organization->id,
            ];
        });
    }

    /**
     * Indicate that the user belongs to a specific organization.
     */
    public function forOrganization(Organization $organization): static
    {
        return $this->state(fn(array $attributes) => [
            'organization_id' => $organization->id,
        ]);
    }

    /**
     * Indicate that the user has a specific role.
     */
    public function withRole(Role $role): static
    {
        return $this->state(fn(array $attributes) => [
            'role_id' => $role->id,
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the model has two-factor authentication configured.
     */
    public function withTwoFactor(): static
    {
        return $this->state(fn(array $attributes) => [
            'two_factor_secret' => Str::random(32),
            'two_factor_recovery_codes' => json_encode([
                Str::random(10),
                Str::random(10),
                Str::random(10),
            ]),
            'two_factor_confirmed_at' => now(),
        ]);
    }

    /**
     * Indicate that the user is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    /**
     * Indicate that the user is suspended.
     */
    public function suspended(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'suspended',
        ]);
    }

    /**
     * Indicate that the user has never logged in.
     */
    public function neverLoggedIn(): static
    {
        return $this->state(fn(array $attributes) => [
            'last_login_at' => null,
        ]);
    }
}

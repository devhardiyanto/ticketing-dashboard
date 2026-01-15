<?php

namespace Database\Factories\Dashboard;

use App\Models\Core\Organization;
use App\Models\Dashboard\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

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
            'organization_id' => null, // Will be set by states
            'status' => 'active',
            'last_login_at' => fake()->dateTimeBetween('-1 month', 'now'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Configure the model factory to assign role after creation.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (User $user) {
            // Role assignment happens via states, not here
        });
    }

    /**
     * Indicate that the user is a super admin.
     */
    public function superAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'organization_id' => null, // Super admin doesn't belong to any org
        ])->afterCreating(function (User $user) {
            $user->assignRole('super_admin');
        });
    }

    /**
     * Indicate that the user is platform staff (internal team).
     */
    public function platformStaff(): static
    {
        return $this->state(fn (array $attributes) => [
            'organization_id' => null, // Platform staff doesn't belong to any org
        ])->afterCreating(function (User $user) {
            $user->assignRole('platform_staff');
        });
    }

    /**
     * Indicate that the user is an organization admin.
     */
    public function orgAdmin(?Organization $organization = null): static
    {
        if (! $organization) {
            throw new \InvalidArgumentException('Organization is required for org_admin users');
        }

        return $this->state(fn (array $attributes) => [
            'organization_id' => $organization->id,
        ])->afterCreating(function (User $user) {
            $user->assignRole('org_admin');
        });
    }

    /**
     * Indicate that the user is organization staff.
     */
    public function orgStaff(?Organization $organization = null): static
    {
        if (! $organization) {
            throw new \InvalidArgumentException('Organization is required for org_staff users');
        }

        return $this->state(fn (array $attributes) => [
            'organization_id' => $organization->id,
        ])->afterCreating(function (User $user) {
            $user->assignRole('org_staff');
        });
    }

    /**
     * Indicate that the user belongs to a specific organization.
     */
    public function forOrganization(Organization $organization): static
    {
        return $this->state(fn (array $attributes) => [
            'organization_id' => $organization->id,
        ]);
    }

    /**
     * Indicate that the user has a specific role by name.
     */
    public function withRole(string $roleName): static
    {
        return $this->afterCreating(function (User $user) use ($roleName) {
            $user->assignRole($roleName);
        });
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the model has two-factor authentication configured.
     */
    public function withTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
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
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    /**
     * Indicate that the user is suspended.
     */
    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'suspended',
        ]);
    }

    /**
     * Indicate that the user has never logged in.
     */
    public function neverLoggedIn(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_login_at' => null,
        ]);
    }
}

<?php

namespace Database\Factories;

use App\Models\DashboardOrganization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DashboardOrganization>
 */
class DashboardOrganizationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = DashboardOrganization::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $businessTypes = ['company', 'individual', 'partnership'];

        return [
            'name' => fake()->company(),
            'business_type' => fake()->randomElement($businessTypes),
            'email' => fake()->unique()->companyEmail(),
            'phone_number' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'tax_id' => fake()->numerify('##.###.###.#-###.###'),
            'logo_url' => fake()->imageUrl(200, 200, 'business', true),
            'status' => 'active',
        ];
    }

    /**
     * Indicate that the organization is a company.
     */
    public function company(): static
    {
        return $this->state(fn (array $attributes) => [
            'business_type' => 'company',
            'name' => fake()->company() . ' Inc.',
        ]);
    }

    /**
     * Indicate that the organization is an individual.
     */
    public function individual(): static
    {
        return $this->state(fn (array $attributes) => [
            'business_type' => 'individual',
            'name' => fake()->name(),
        ]);
    }

    /**
     * Indicate that the organization is a partnership.
     */
    public function partnership(): static
    {
        return $this->state(fn (array $attributes) => [
            'business_type' => 'partnership',
            'name' => fake()->company() . ' & Partners',
        ]);
    }

    /**
     * Indicate that the organization is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    /**
     * Indicate that the organization is suspended.
     */
    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'suspended',
        ]);
    }

    /**
     * Indicate that the organization has no logo.
     */
    public function withoutLogo(): static
    {
        return $this->state(fn (array $attributes) => [
            'logo_url' => null,
        ]);
    }

    /**
     * Indicate that the organization has no tax ID.
     */
    public function withoutTaxId(): static
    {
        return $this->state(fn (array $attributes) => [
            'tax_id' => null,
        ]);
    }

    /**
     * Indicate that the organization has no address.
     */
    public function withoutAddress(): static
    {
        return $this->state(fn (array $attributes) => [
            'address' => null,
        ]);
    }
}

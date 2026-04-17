<?php

namespace Database\Factories;

use App\Enums\PolicyStatus;
use App\Models\Customer;
use App\Models\InsuranceCompany;
use App\Models\Policy;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Policy>
 */
class PolicyFactory extends Factory
{
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-1 year', 'now');
        $endDate = fake()->dateTimeBetween('+1 day', '+2 years');
        $premium = fake()->randomFloat(2, 500, 10000);

        return [
            'customer_id' => Customer::factory(),
            'vehicle_id' => Vehicle::factory(),
            'insurer_id' => InsuranceCompany::factory(),
            'policy_number' => fake()->unique()->numerify('POL-######'),
            'status' => PolicyStatus::Active,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'premium' => $premium,
            'commission_percentage' => null,
            'commission_value' => null,
            'renewed_from_id' => null,
            'notes' => null,
            'cancelled_at' => null,
        ];
    }

    public function active(): static
    {
        return $this->state([
            'status' => PolicyStatus::Active,
            'cancelled_at' => null,
            'end_date' => fake()->dateTimeBetween('+31 days', '+2 years')->format('Y-m-d'),
        ]);
    }

    public function expired(): static
    {
        return $this->state([
            'status' => PolicyStatus::Expired,
            'end_date' => fake()->dateTimeBetween('-6 months', '-1 day')->format('Y-m-d'),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state([
            'status' => PolicyStatus::Cancelled,
            'cancelled_at' => now()->subDays(fake()->numberBetween(1, 30)),
        ]);
    }

    public function renewed(): static
    {
        return $this->state([
            'status' => PolicyStatus::Renewed,
        ]);
    }

    public function expiringSoon(): static
    {
        return $this->state([
            'status' => PolicyStatus::Active,
            'end_date' => now()->addDays(fake()->numberBetween(1, 14))->toDateString(),
        ]);
    }
}

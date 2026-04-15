<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vehicle>
 */
class VehicleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $letters = strtoupper(fake()->regexify('[A-Z]{3}'));
        $digits = fake()->numerify('####');
        $licensePlate = "{$letters}-{$digits}";

        return [
            'customer_id' => Customer::factory(),
            'license_plate' => $licensePlate,
            'brand' => fake()->randomElement(['Volkswagen', 'Fiat', 'Chevrolet', 'Ford', 'Toyota', 'Honda', 'Hyundai']),
            'model' => fake()->word(),
            'model_year' => (string) fake()->numberBetween(2000, 2026),
            'fipe' => null,
            'usage' => fake()->randomElement(array_keys(Vehicle::usageOptions())),
            'color' => fake()->randomElement(array_keys(Vehicle::colorOptions())),
        ];
    }
}

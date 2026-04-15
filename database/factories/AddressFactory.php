<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Address>
 */
class AddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'street' => fake()->streetName(),
            'zip_code' => fake()->numerify('#####-###'),
            'neighborhood' => fake()->word(),
            'state' => fake()->stateAbbr(),
            'city' => fake()->city(),
            'number' => fake()->buildingNumber(),
            'complement' => null,
            'status' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['status' => false]);
    }
}

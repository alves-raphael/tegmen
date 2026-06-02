<?php

namespace Database\Factories;

use App\Enums\CustomerType;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->firstName().' '.fake()->lastName(),
            'type' => CustomerType::Person,
            'document' => $this->validCpfDigits(),
            'cpf' => null,
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('(##) #####-####'),
            'birth_date' => fake()->date(),
        ];
    }

    public function company(): static
    {
        return $this->state(fn () => [
            'type' => CustomerType::Company,
            'document' => $this->validCnpjDigits(),
            'birth_date' => null,
        ]);
    }

    /**
     * Generate a valid CPF as raw digits (no mask).
     */
    private function validCpfDigits(): string
    {
        do {
            $n = array_map(fn () => random_int(0, 9), range(1, 9));
        } while (count(array_unique($n)) === 1);

        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += $n[$i] * (10 - $i);
        }
        $r = $sum % 11;
        $n[] = $r < 2 ? 0 : 11 - $r;

        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += $n[$i] * (11 - $i);
        }
        $r = $sum % 11;
        $n[] = $r < 2 ? 0 : 11 - $r;

        return implode('', $n);
    }

    /**
     * Generate a valid CNPJ as raw digits (no mask).
     */
    private function validCnpjDigits(): string
    {
        do {
            $n = array_map(fn () => random_int(0, 9), range(1, 12));
        } while (count(array_unique($n)) === 1);

        $firstMultipliers = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += $n[$i] * $firstMultipliers[$i];
        }
        $r = $sum % 11;
        $n[] = $r < 2 ? 0 : 11 - $r;

        $secondMultipliers = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 13; $i++) {
            $sum += $n[$i] * $secondMultipliers[$i];
        }
        $r = $sum % 11;
        $n[] = $r < 2 ? 0 : 11 - $r;

        return implode('', $n);
    }
}

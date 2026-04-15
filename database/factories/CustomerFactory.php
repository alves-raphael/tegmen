<?php

namespace Database\Factories;

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
            'cpf' => $this->validCpf(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('(##) #####-####'),
            'birth_date' => fake()->date(),
        ];
    }

    /**
     * Generate a CPF string with valid check digits, formatted as ###.###.###-##.
     */
    private function validCpf(): string
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

        return sprintf('%d%d%d.%d%d%d.%d%d%d-%d%d', ...$n);
    }
}

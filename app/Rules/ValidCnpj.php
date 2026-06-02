<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class ValidCnpj implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $digits = preg_replace('/\D/', '', (string) $value);

        if (strlen($digits) !== 14 || preg_match('/^(\d)\1{13}$/', $digits)) {
            $fail(__('O CNPJ informado não é válido.'));

            return;
        }

        $firstMultipliers = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $secondMultipliers = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

        foreach ([$firstMultipliers, $secondMultipliers] as $index => $multipliers) {
            $sum = 0;
            for ($i = 0; $i < count($multipliers); $i++) {
                $sum += (int) $digits[$i] * $multipliers[$i];
            }
            $remainder = $sum % 11;
            $expected = $remainder < 2 ? 0 : 11 - $remainder;

            if ((int) $digits[12 + $index] !== $expected) {
                $fail(__('O CNPJ informado não é válido.'));

                return;
            }
        }
    }
}

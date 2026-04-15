<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class ValidCpf implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $digits = preg_replace('/\D/', '', (string) $value);

        if (strlen($digits) !== 11 || preg_match('/^(\d)\1{10}$/', $digits)) {
            $fail(__('O CPF informado não é válido.'));

            return;
        }

        for ($i = 9; $i < 11; $i++) {
            $sum = 0;
            for ($j = 0; $j < $i; $j++) {
                $sum += (int) $digits[$j] * ($i + 1 - $j);
            }
            $remainder = $sum % 11;
            $expected = $remainder < 2 ? 0 : 11 - $remainder;

            if ((int) $digits[$i] !== $expected) {
                $fail(__('O CPF informado não é válido.'));

                return;
            }
        }
    }
}

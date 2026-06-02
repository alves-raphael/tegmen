<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class ValidModelYear implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $maxYear = (int) date('Y') + 1;
        $parts = explode('/', (string) $value);

        foreach ($parts as $part) {
            $year = (int) $part;
            if ($year < 1900 || $year > $maxYear) {
                $fail(__('O ano do modelo deve estar entre 1900 e :max.', ['max' => $maxYear]));

                return;
            }
        }

        if (count($parts) === 2 && (int) $parts[1] < (int) $parts[0]) {
            $fail(__('O segundo ano não pode ser menor que o primeiro.'));
        }
    }
}

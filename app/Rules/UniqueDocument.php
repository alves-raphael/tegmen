<?php

namespace App\Rules;

use App\Models\Customer;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class UniqueDocument implements ValidationRule
{
    public function __construct(private readonly ?int $ignoreId = null) {}

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $digits = preg_replace('/\D/', '', (string) $value);

        $exists = Customer::where('document', $digits)
            ->when($this->ignoreId, fn ($q) => $q->where('id', '<>', $this->ignoreId))
            ->exists();

        if ($exists) {
            $fail(__('Este documento já está cadastrado.'));
        }
    }
}

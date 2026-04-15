<?php

namespace App\Concerns;

use App\Rules\ValidCpf;
use Illuminate\Contracts\Validation\ValidationRule;

trait CustomerValidationRules
{
    /**
     * @return array<string, array<int, ValidationRule|string>>
     */
    protected function step1Rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:255', 'regex:/^[\p{L}\s\'\-]+$/u'],
            'cpf' => ['required', 'string', 'regex:/^\d{3}\.\d{3}\.\d{3}-\d{2}$/', new ValidCpf],
            'email' => ['required', 'string', 'email', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^\(\d{2}\) \d{4,5}-\d{4}$/'],
            'birth_date' => ['required', 'string', 'date_format:d/m/Y'],
        ];
    }

    /**
     * @return array<string, array<int, ValidationRule|string>>
     */
    protected function step2Rules(): array
    {
        return [
            'street' => ['required', 'string', 'max:255'],
            'zip_code' => ['required', 'string', 'regex:/^\d{5}-\d{3}$/'],
            'neighborhood' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'size:2'],
            'city' => ['required', 'string', 'max:255'],
            'number' => ['required', 'string', 'max:20'],
            'complement' => ['nullable', 'string', 'max:255'],
        ];
    }
}

<?php

namespace App\Concerns;

use Illuminate\Validation\Rule;

trait PolicyValidationRules
{
    /**
     * Rules for editing: only commission and notes fields are writable.
     *
     * @return array<string, array<int, mixed>>
     */
    protected function editRules(): array
    {
        return [
            'commission_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'commission_value' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function policyRules(): array
    {
        return [
            'policy_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('policies', 'policy_number')
                    ->where('insurer_id', (int) $this->insurer_id),
            ],
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'vehicle_id' => ['required', 'integer', 'exists:vehicles,id'],
            'insurer_id' => ['required', 'integer', 'exists:insurance_companies,id'],
            'start_date' => ['required', 'string', 'date_format:d/m/Y'],
            'end_date' => ['required', 'string', 'date_format:d/m/Y', 'after:today'],
            'premium' => ['required', 'numeric', 'min:0.01'],
            'commission_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'commission_value' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}

<?php

namespace App\Concerns;

use App\Models\Vehicle;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

trait VehicleValidationRules
{
    /**
     * @return array<string, array<int, ValidationRule|string>>
     */
    protected function vehicleRules(): array
    {
        return [
            'license_plate' => ['required', 'string', 'regex:/^[A-Z]{3}-\d{4}$|^[A-Z]{3}\d[A-Z]\d{2}$/'],
            'brand' => ['required', 'string', 'max:100'],
            'model' => ['required', 'string', 'max:100'],
            'model_year' => ['required', 'digits:4', 'integer', 'min:1900', 'max:'.((int) date('Y') + 1)],
            'fipe' => ['nullable', 'string', 'max:20'],
            'usage' => ['required', Rule::in(array_keys(Vehicle::usageOptions()))],
            'color' => ['required', 'string', 'max:50'],
        ];
    }
}

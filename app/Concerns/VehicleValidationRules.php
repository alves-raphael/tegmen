<?php

namespace App\Concerns;

use App\Models\Vehicle;
use App\Rules\ValidModelYear;
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
            'model_year' => ['required', 'regex:/^\d{4}(\/\d{4})?$/', new ValidModelYear],
            'fipe' => ['nullable', 'string', 'max:20'],
            'usage' => ['required', Rule::in(array_keys(Vehicle::usageOptions()))],
            'color' => ['required', 'string', 'max:50'],
        ];
    }
}

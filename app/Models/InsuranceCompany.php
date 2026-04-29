<?php

namespace App\Models;

use Database\Factories\InsuranceCompanyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'status'])]
class InsuranceCompany extends Model
{
    /** @use HasFactory<InsuranceCompanyFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }

    public function policies(): HasMany
    {
        return $this->hasMany(Policy::class, 'insurer_id');
    }
}

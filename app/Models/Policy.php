<?php

namespace App\Models;

use App\Enums\PolicyStatus;
use Database\Factories\PolicyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'customer_id', 'vehicle_id', 'insurer_id', 'policy_number',
    'status', 'start_date', 'end_date',
    'premium', 'commission_percentage', 'commission_value',
    'renewed_from_id', 'notes', 'cancelled_at',
])]
class Policy extends Model
{
    /** @use HasFactory<PolicyFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => PolicyStatus::class,
            'start_date' => 'date',
            'end_date' => 'date',
            'premium' => 'decimal:2',
            'commission_percentage' => 'decimal:2',
            'commission_value' => 'decimal:2',
            'cancelled_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function insurer(): BelongsTo
    {
        return $this->belongsTo(InsuranceCompany::class, 'insurer_id');
    }

    public function renewedFrom(): BelongsTo
    {
        return $this->belongsTo(Policy::class, 'renewed_from_id');
    }

    public function renewal(): HasOne
    {
        return $this->hasOne(Policy::class, 'renewed_from_id');
    }

    public function belongsToUser(int $userId): bool
    {
        return $this->customer->user_id === $userId;
    }
}

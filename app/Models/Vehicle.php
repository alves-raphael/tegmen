<?php

namespace App\Models;

use Database\Factories\VehicleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['customer_id', 'license_plate', 'brand', 'model', 'model_year', 'fipe', 'usage', 'color'])]
class Vehicle extends Model
{
    /** @use HasFactory<VehicleFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    public static function usageOptions(): array
    {
        return [
            'personal' => __('Particular'),
            'rideshare' => __('Aplicativo'),
            'work' => __('Trabalho'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function colorOptions(): array
    {
        return [
            'white' => __('Branco'),
            'black' => __('Preto'),
            'silver' => __('Prata'),
            'gray' => __('Cinza'),
            'blue' => __('Azul'),
            'red' => __('Vermelho'),
            'other' => __('Outra'),
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}

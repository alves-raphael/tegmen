<?php

namespace App\Enums;

enum PolicyStatus: string
{
    case Active = 'ACTIVE';
    case Renewed = 'RENEWED';
    case Cancelled = 'CANCELLED';
    case Expired = 'EXPIRED';

    public function label(): string
    {
        return match ($this) {
            self::Active => __('Ativa'),
            self::Renewed => __('Renovada'),
            self::Cancelled => __('Cancelada'),
            self::Expired => __('Expirada'),
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Active => 'green',
            self::Renewed => 'sky',
            self::Cancelled, self::Expired => 'red',
        };
    }

    public function isCancellable(): bool
    {
        return $this === self::Active;
    }

    public function isRenewable(): bool
    {
        return $this === self::Active;
    }
}

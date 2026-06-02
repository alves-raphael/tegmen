<?php

namespace App\Enums;

enum CustomerType: string
{
    case Person = 'person';
    case Company = 'company';

    public function label(): string
    {
        return match ($this) {
            CustomerType::Person => __('Pessoa Física'),
            CustomerType::Company => __('Pessoa Jurídica'),
        };
    }
}

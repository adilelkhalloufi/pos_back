<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum EnumPayementStatue: int implements HasLabel
{
    public function getLabel(): ?string
    {
        return match ($this) {
            self::UNPAID => 'impayé',
            self::AVANCE => 'Avance',
            self::PAID => 'Payé',

        };
    }

    case UNPAID = 1;
    case AVANCE = 2;
    case PAID = 3;

}

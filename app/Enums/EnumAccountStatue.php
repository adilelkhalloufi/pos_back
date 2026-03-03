<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum EnumAccountStatue: int implements HasLabel
{
    public function getLabel(): ?string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::INACTIVE => 'Inactive',
            self::BANKRUPT => 'Bankrupt',

        };
    }

  public function getColor(): string|array|null
    {
        return match ($this) {
            self::ACTIVE => 'gray',
            self::INACTIVE => 'warning',
            self::BANKRUPT => 'success',
         };
    }
    case ACTIVE = 1;
    case INACTIVE = 2;
    case BANKRUPT = 3;

}

<?php

namespace App\Enums;

enum AttendanceCode: string
{
    case PRESENT = 'P';
    case REST = 'R';
    case HOLIDAY = 'FE';
    case FORMATION = 'F';
    case FORMATION_WORK = 'FT';
    case PAID_LEAVE = 'CP';
    case PAID_ABSENCE = 'AP';
    case UNPAID_ABSENCE = 'A';
    case SICK = 'M';
    case SICK_CM = 'CM';
    case MATERNITY = 'MP';
    case RECOVERY = 'RCP';
    case DOUBLE_SHIFT = 'DC';

    public function label(): string
    {
        return match ($this) {
            self::PRESENT => 'Présent',
            self::REST => 'Repos',
            self::HOLIDAY => 'Férié',
            self::FORMATION => 'Formation',
            self::FORMATION_WORK => 'Formation/FT',
            self::PAID_LEAVE => 'Congé payé',
            self::PAID_ABSENCE => 'Absence payée',
            self::UNPAID_ABSENCE => 'Absence non payée',
            self::SICK => 'Maladie',
            self::SICK_CM => 'Congé maladie',
            self::MATERNITY => 'Maternité',
            self::RECOVERY => 'Récupération',
            self::DOUBLE_SHIFT => 'Double shift',
        };
    }

    public function isPresentByDefault(): bool
    {
        return match ($this) {
            self::PRESENT, self::DOUBLE_SHIFT, self::FORMATION, self::FORMATION_WORK => true,
            default => false,
        };
    }

    public function defaultWorkUnits(): float
    {
        return match ($this) {
            self::PRESENT => 1.0,
            self::DOUBLE_SHIFT => 2.0,
            default => 0.0,
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(
            static fn (self $code): string => $code->value,
            self::cases()
        );
    }
}


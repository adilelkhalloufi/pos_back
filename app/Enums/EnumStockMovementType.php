<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum EnumStockMovementType: string implements HasLabel
{
    case ENTRY = 'entry';
    case EXIT = 'exit';
    case TRANSFER = 'transfer';
    case ADJUSTMENT = 'adjustment';
    case DESTRUCTION = 'destruction';
    case PURCHASE = 'purchase';
    case SALE = 'sale';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::ENTRY => 'Entrée de stock',
            self::EXIT => 'Sortie de stock',
            self::TRANSFER => 'Transfert',
            self::ADJUSTMENT => 'Ajustement',
            self::DESTRUCTION => 'Destruction',
            self::PURCHASE => 'Achat',
            self::SALE => 'Vente',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::ENTRY => 'green',
            self::EXIT => 'red',
            self::TRANSFER => 'blue',
            self::ADJUSTMENT => 'orange',
            self::DESTRUCTION => 'gray',
            self::PURCHASE => 'emerald',
            self::SALE => 'purple',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::ENTRY => 'heroicon-o-arrow-down-tray',
            self::EXIT => 'heroicon-o-arrow-up-tray',
            self::TRANSFER => 'heroicon-o-arrow-right-circle',
            self::ADJUSTMENT => 'heroicon-o-adjustments-horizontal',
            self::DESTRUCTION => 'heroicon-o-trash',
            self::PURCHASE => 'heroicon-o-shopping-cart',
            self::SALE => 'heroicon-o-banknotes',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::ENTRY => 'Mouvement d\'entrée de stock',
            self::EXIT => 'Mouvement de sortie de stock',
            self::TRANSFER => 'Transfert entre entrepôts',
            self::ADJUSTMENT => 'Ajustement d\'inventaire',
            self::DESTRUCTION => 'Destruction de stock',
            self::PURCHASE => 'Entrée via commande d\'achat',
            self::SALE => 'Sortie via vente',
        };
    }
}

<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum EnumOrderStatue: int implements HasLabel
{

    case PENDING = 1;
    case WAIT_PAYMENT = 2;
    case WAIT_FULFILLMENT = 3;
    case WAIT_SHIPMENT = 4;
    case WAIT_PICKUP = 5;
    case PARTIAL_SHIPPED = 6;
    case COMPLETED = 7;
    case SHIPPED = 8;
    case CANCELLED = 9;
    case DECLINED = 10;
    case REFUNDED = 11;
    case DISPUTED = 12;
    case MANUAL_VERIFICATION_REQUIRED = 13;
    case PARTIAL_REFUNDED = 14;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PENDING => 'En attente',
            self::WAIT_PAYMENT => 'En attente de paiement',
            self::WAIT_FULFILLMENT => 'En attente de traitement',
            self::WAIT_SHIPMENT => 'En attente d\'expédition',
            self::WAIT_PICKUP => 'En attente de retrait',
            self::PARTIAL_SHIPPED => 'Partiellement expédié',
            self::COMPLETED => 'Terminé',
            self::SHIPPED => 'Expédié',
            self::CANCELLED => 'Annulé',
            self::DECLINED => 'Refusé',
            self::REFUNDED => 'Remboursé',
            self::DISPUTED => 'Contesté',
            self::MANUAL_VERIFICATION_REQUIRED => 'Vérification manuelle requise',
            self::PARTIAL_REFUNDED => 'Remboursé partiellement',
     
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::PENDING => 'gray',
            self::WAIT_PAYMENT => 'orange',
            self::WAIT_FULFILLMENT => 'blue',
            self::WAIT_SHIPMENT => 'purple',
            self::WAIT_PICKUP => 'yellow',
            self::PARTIAL_SHIPPED => 'indigo',
            self::COMPLETED => 'green',
            self::SHIPPED => 'pink',
            self::CANCELLED => 'red',
            self::DECLINED => 'destructive',
            self::REFUNDED => 'secondary',
            self::DISPUTED => 'brown',
            self::MANUAL_VERIFICATION_REQUIRED => 'darkorange',
            self::PARTIAL_REFUNDED => 'lightyellow',
        };
    }
 
}
 
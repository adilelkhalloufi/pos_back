<?php

namespace App\Services\Payement;

use App\Enums\EnumPayementStatue;
use App\Models\OrderSale;
use App\Models\Payemnt;
use App\Repositories\Payement\PayementRepository;

class PayementService
{
    public function __construct(
        private readonly PayementRepository $payementRepository,
    ) {}

    public function findById(int $id)
    {
        return $this->payementRepository->find($id);
    }

    public function findwithRelations(int $id, array $relations)
    {
        return $this->payementRepository->findWith($id, Payemnt::COL_ID, $relations);
    }

    public function create(float $total_payment, OrderSale $order): Payemnt
    {
        return $this->payementRepository->create([
            Payemnt::COL_MODE_PAYEMNT_ID => 1,
            Payemnt::COL_AMOUNT => $total_payment,
            Payemnt::COL_ORDER_ID => $order->getAttribute(OrderSale::COL_ID),
            Payemnt::COL_CUSTOMER_ID => $order->getAttribute(OrderSale::COL_CUSTOMER_ID),
            Payemnt::COL_USER_ID => $order->getAttribute(OrderSale::COL_USER_ID),
            Payemnt::COL_STORE_ID => $order->getAttribute(OrderSale::COL_STORE_ID),
            Payemnt::COL_STATUS => $order->getAttribute(OrderSale::COL_STATUS),
        ]);
    }

   
}

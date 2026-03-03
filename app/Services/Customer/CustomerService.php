<?php

namespace App\Services\Customer;

use App\Models\Customer;
use App\Models\OrderSale;
use App\Repositories\Customer\CustomerRepository;

class CustomerService
{
    public function __construct(
        private readonly CustomerRepository $customerRepository
    ) {}

    public function findById(int $id)
    {
        return $this->customerRepository->find($id);
    }

    public function getCustomersByStoreId(int $storeId)
    {
        return $this->customerRepository->getCustomersForStore($storeId);
    }

    public function create(array $attributes): Customer | null
    {
        // for birthday sometime it come just age sometimes date need to handle that
        if (isset($attributes[Customer::COL_BIRTHDAY])) {
            $birthday = $attributes[Customer::COL_BIRTHDAY];
            if (is_numeric($birthday)) {
                $birthYear = now()->subYears((int)$birthday)->year;
                $attributes[Customer::COL_BIRTHDAY] = now()->setYear($birthYear)->toDateString();
            }
        }


        return $this->customerRepository->create([
            Customer::COL_NAME => $attributes[Customer::COL_NAME],
            Customer::COL_EMAIL => $attributes[Customer::COL_EMAIL] ?? null,
            Customer::COL_PHONE => $attributes[Customer::COL_PHONE] ?? null,
            Customer::COL_GENDER => $attributes[Customer::COL_GENDER] ?? true,
            Customer::COL_BIRTHDAY => $attributes[Customer::COL_BIRTHDAY] ?? null,
            Customer::COL_STORE_ID => currentStoreId(),
            Customer::COL_USER_ID => auth()->id(),
        ]);
    }

    public function update(int $id, array $attributes): bool
    {

        return $this->customerRepository->update($id, [
            Customer::COL_NAME => $attributes[Customer::COL_NAME],
            Customer::COL_EMAIL => $attributes[Customer::COL_EMAIL] ?? null,
            Customer::COL_PHONE => $attributes[Customer::COL_PHONE] ?? null,
            Customer::COL_GENDER => $attributes[Customer::COL_GENDER] ?? true,
            Customer::COL_BIRTHDAY => $attributes[Customer::COL_BIRTHDAY] ?? null,
        ]);
    }

    public function delete(int $id): bool
    {
        return $this->customerRepository->delete($id);
    }

    public function updateTotals(Customer $customer): void
    {
        $customer->total_orders = $customer->orders()->count();
        $customer->total_prescriptions = $customer->prescriptions()->count();

        $customer->total_payments = $customer->payments()->sum('amount');
        $customer->total_amount_orders = $customer->orders()->sum(OrderSale::COL_TOTAL_COMMAND);
        $customer->last_order_date = now();

        $customer->save();
    }

    public function findOrCreate(array $attributes): Customer
    {
        $customer = null;
        if (isset($attributes['id'])) {
            $customer = $this->findById($attributes['id']);

            if ($customer) {
                return $customer;
            }
        }
        return $this->create($attributes);
    }

    public function getCustomersWithRelations(int $id, array $relations)
    {
        return $this->customerRepository->findWith($id, Customer::COL_ID, $relations);
    }
}

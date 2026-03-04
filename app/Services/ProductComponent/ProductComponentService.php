<?php

namespace App\Services\ProductComponent;

use App\Models\ProductComponent;

class ProductComponentService
{
    public function forProduct(int $productId)
    {
        return ProductComponent::where(ProductComponent::COL_PRODUCT_ID, $productId)
            ->with(['component', 'unit'])
            ->get();
    }

    public function create(int $productId, array $attributes): ProductComponent
    {
        return ProductComponent::create([
            ProductComponent::COL_PRODUCT_ID   => $productId,
            ProductComponent::COL_COMPONENT_ID => $attributes[ProductComponent::COL_COMPONENT_ID],
            ProductComponent::COL_QUANTITY     => $attributes[ProductComponent::COL_QUANTITY],
            ProductComponent::COL_UNIT_ID      => $attributes[ProductComponent::COL_UNIT_ID] ?? null,
            ProductComponent::COL_NOTE         => $attributes[ProductComponent::COL_NOTE] ?? null,
        ]);
    }

    public function update(int $id, array $attributes): ProductComponent
    {
        $component = ProductComponent::findOrFail($id);
        $component->update([
            ProductComponent::COL_QUANTITY => $attributes[ProductComponent::COL_QUANTITY] ?? $component->quantity,
            ProductComponent::COL_UNIT_ID  => $attributes[ProductComponent::COL_UNIT_ID] ?? $component->unit_id,
            ProductComponent::COL_NOTE     => $attributes[ProductComponent::COL_NOTE] ?? $component->note,
        ]);
        return $component->fresh(['component', 'unit']);
    }

    public function delete(int $id): void
    {
        ProductComponent::findOrFail($id)->delete();
    }
}

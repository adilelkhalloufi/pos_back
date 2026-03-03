<?php

namespace App\Services\Category;

use App\Models\Category;
use App\Repositories\Category\CategoryRepository;

class CategoryService
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository
    ) {}


    public function findById(int $id)
    {
        return $this->categoryRepository->find($id);
    }

    public function findByStoreId(int $storeId)
    {
        return $this->categoryRepository->findByStoreIdOrNull($storeId);
    }

    public function create(array $attributes): ?Category
    {
        $store = $this->categoryRepository->create([
            Category::COL_NAME => $attributes[Category::COL_NAME],
            // Category::COL_DESCRIPTION => $attributes[Category::COL_DESCRIPTION],
            Category::COL_STORE_ID => currentStoreId(),
            Category::COL_USER_ID => auth()->id(),
        ]);

        return $store;
    }

    public function update(int $id, array $attributes): ?bool
    {
        $category = $this->categoryRepository->find($id);
        if (!$category) {
            return null;
        }

        $category = $this->categoryRepository->update($id, [
            Category::COL_NAME => $attributes[Category::COL_NAME],
            Category::COL_DESCRIPTION => $attributes[Category::COL_DESCRIPTION],
        ]);

        return $category;
    }
}

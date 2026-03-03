<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\BaseController;
use App\Models\Category;
use App\Services\Category\CategoryService;
use Illuminate\Http\Request;

class CategoryController extends BaseController
{

    private  CategoryService $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        parent::__construct();
        $this->categoryService = $categoryService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Get all categories for the store and the category has no store bring too
       $categories =  $this->categoryService->findByStoreId(currentStoreId());

       return response()->json($categories, 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            Category::COL_NAME => 'required',
        ]);

        $category = $this->categoryService->create($validatedData);
        return response()->json($category, 201);
    }

  
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            Category::COL_NAME => 'required',
        ]);

        $category = $this->categoryService->update($id, $validatedData);  

        return response()->json([
            'message' => 'Category updated successfully',
        
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $category = Category::find($id);
        $category->delete();

        return response()->json(null, 204);
    }
}

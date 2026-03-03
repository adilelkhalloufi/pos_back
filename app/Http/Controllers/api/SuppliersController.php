<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\BaseController;
use App\Http\Resources\SupplierResource;
 use App\Models\Suppliers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuppliersController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {


        $suppliers = $this->the_store()->suppliers()->orderByDesc('id')->get();


        return response()->json(SupplierResource::collection($suppliers));
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
        $valideData = $request->validate([
            Suppliers::COL_COMPANY_NAME => 'required',
            Suppliers::COL_PHONE => 'nullable',
            Suppliers::COL_EMAIL => 'nullable',
            Suppliers::COL_CITY => 'nullable',
            Suppliers::COL_ADDRESS => 'nullable',
        ]);

        
        $suppliers = $this->the_store()->suppliers()->create($valideData);
        return response()->json($suppliers, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Suppliers $suppliers)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Suppliers $suppliers) {}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $valideData = $request->validate([
            Suppliers::COL_COMPANY_NAME => 'required',
            Suppliers::COL_STORE_ID => 'required',
            Suppliers::COL_PHONE => 'nullable',
            Suppliers::COL_EMAIL => 'nullable',
            Suppliers::COL_CITY => 'nullable',
            Suppliers::COL_ADDRESS => 'nullable',
        ]);

        $suppliers = Suppliers::find($id);
        $suppliers->update($valideData);

        return response()->json($suppliers);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {

        $suppliers = Suppliers::find($id);
        if (Auth::user()->id != $suppliers->user_id) {
            return response()->json(['error' => 'You can only delete your own suppliers.'], 403);
        }
        $suppliers->delete();

        return response()->json(['message' => 'Supplier deleted successfully']);
    }
}

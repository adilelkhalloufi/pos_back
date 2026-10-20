<?php

namespace App\Http\Controllers\api;

use App\Http\Resources\StoreResource;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class StoreController
{
    public function index()
    {
        $stores = Auth::user()->stores()->with('cites')->get();

        return response()->json(StoreResource::collection($stores));
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            Store::COL_NAME => 'required',
            Store::COL_PHONE => 'required',
            Store::COL_ADDRESS => 'nullable',
            Store::COL_LOGO => 'nullable',
            Store::COL_WEBSITE => 'nullable',
            Store::COL_IF => 'nullable',
            Store::COL_ICE => 'nullable',
            Store::COL_RC => 'nullable',
            Store::COL_PATENTE => 'nullable',
            Store::COL_CNSS => 'nullable',
            Store::COL_TAX => 'nullable',
            Store::COL_LATITUDE => 'nullable',
            Store::COL_LONGITUDE => 'nullable',
            Store::COL_EMAIL => 'nullable|email',
            Store::COL_ZIP_CODE => 'nullable',
            Store::COL_CITY => 'nullable',
            Store::COL_PUBLIC_SLUG => 'nullable|string|max:120|unique:stores,public_slug,' . $id,
            Store::COL_STOREFRONT_ENABLED => 'nullable|boolean',
            Store::COL_STOREFRONT_TITLE => 'nullable|string|max:255',
            Store::COL_STOREFRONT_DESCRIPTION => 'nullable|string|max:3000',
            Store::COL_STOREFRONT_BANNER => 'nullable|string|max:255',
        ]);

        if (array_key_exists(Store::COL_PUBLIC_SLUG, $validatedData) && empty($validatedData[Store::COL_PUBLIC_SLUG])) {
            $validatedData[Store::COL_PUBLIC_SLUG] = Str::slug(($validatedData[Store::COL_NAME] ?? '') . '-' . $id);
        }
        $store = Auth::user()
            ->stores()
            ->find($id);

        $store->update($validatedData);

        return response()->json($store);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            Store::COL_NAME => 'required',

        ]);

        $store = Auth::user()
            ->stores()
            ->create($request->all());

        return response()->json($store);
    }

    public function delete($id)
    {
        $store = Auth::user()
            ->stores()
            ->find($id);

        $store->delete();

        return response()->json($store);
    }
}

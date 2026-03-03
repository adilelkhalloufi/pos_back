<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\BaseController;
use App\Http\Resources\ModePayementResource;
use App\Models\ModePayemnt;
use App\Models\PayementTermes;
use Illuminate\Http\Request;

class ModePayemntController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {   
        return response()->json([
            'modes' => ModePayementResource::collection(ModePayemnt::all()),
            'termes' => ModePayementResource::collection(PayementTermes::all()),
        ]);
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(ModePayemnt $modePayemnt)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ModePayemnt $modePayemnt)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ModePayemnt $modePayemnt)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ModePayemnt $modePayemnt)
    {
        //
    }
}

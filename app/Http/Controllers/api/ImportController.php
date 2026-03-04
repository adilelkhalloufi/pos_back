<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\BaseController;
use App\Models\Import;
use App\Services\Import\ProductImportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ImportController extends BaseController
{
    public function __construct(private readonly ProductImportService $importService)
    {
        parent::__construct();
    }

    /** GET /imports — list imports for the store */
    public function index()
    {
        $imports = Import::where(Import::COL_STORE_ID, $this->storeId())
            ->orderByDesc(Import::COL_ID)
            ->get();
        return response()->json($imports, Response::HTTP_OK);
    }

    /** POST /imports/upload — upload CSV/XLSX, validate rows, return dry-run result */
    public function upload(Request $request)
    {
        $request->validate([
            'file'        => 'required|file|mimes:csv,txt,xlsx|max:10240',
            'supplier_id' => 'nullable|integer|exists:suppliers,id',
        ]);

        try {
            $import = $this->importService->upload(
                $request->file('file'),
                $request->input('supplier_id')
            );
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return response()->json($import, Response::HTTP_CREATED);
    }

    /** PUT /imports/{import}/commit — commit a validated import */
    public function commit(int $id)
    {
        $import = Import::where(Import::COL_STORE_ID, $this->storeId())->findOrFail($id);

        try {
            $import = $this->importService->commit($import);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_CONFLICT);
        }

        return response()->json([
            'message' => 'Import committed.',
            'import'  => $import,
        ], Response::HTTP_OK);
    }

    /** GET /imports/{import} — detail with rows */
    public function show(int $id)
    {
        $import = Import::where(Import::COL_STORE_ID, $this->storeId())
            ->with('rows')
            ->findOrFail($id);
        return response()->json($import, Response::HTTP_OK);
    }
}

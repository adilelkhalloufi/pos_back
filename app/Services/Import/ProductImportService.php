<?php

namespace App\Services\Import;

use App\Models\Import;
use App\Models\ImportRow;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;

class ProductImportService
{
    /**
     * Upload CSV/XLSX file, parse rows, validate, store as "pending" import.
     */
    public function upload(UploadedFile $file, ?int $supplierId): Import
    {
        $path = $file->store('imports', 'local');

        $import = Import::create([
            Import::COL_TYPE        => 'products',
            Import::COL_FILE_PATH   => $path,
            Import::COL_STATUS      => Import::STATUS_PENDING,
            Import::COL_SUPPLIER_ID => $supplierId,
            Import::COL_USER_ID     => auth()->id(),
            Import::COL_STORE_ID    => currentStoreId(),
        ]);

        $rows = $this->parseFile(storage_path('app/' . $path));
        $import->update([Import::COL_TOTAL_ROWS => count($rows)]);

        $validCount = 0;
        $errorCount = 0;

        foreach ($rows as $index => $row) {
            $errors = $this->validateRow($row);
            $status = empty($errors) ? 'valid' : 'error';
            empty($errors) ? $validCount++ : $errorCount++;

            ImportRow::create([
                ImportRow::COL_IMPORT_ID   => $import->id,
                ImportRow::COL_ROW_NUMBER  => $index + 1,
                ImportRow::COL_RAW_DATA    => $row,
                ImportRow::COL_ERRORS      => $errors ?: null,
                ImportRow::COL_STATUS      => $status,
            ]);
        }

        $import->update([
            Import::COL_VALID_ROWS => $validCount,
            Import::COL_ERROR_ROWS => $errorCount,
            Import::COL_STATUS     => $errorCount > 0 ? Import::STATUS_PENDING : Import::STATUS_VALIDATED,
        ]);

        return $import->load('rows');
    }

    /**
     * Commit a validated import: create/update products from valid rows.
     */
    public function commit(Import $import): Import
    {
        if (!in_array($import->status, [Import::STATUS_PENDING, Import::STATUS_VALIDATED])) {
            throw new \RuntimeException('Import is not in a committable state.');
        }

        $storeId = $import->store_id;
        $committed = 0;

        DB::transaction(function () use ($import, $storeId, &$committed) {
            $rows = $import->rows()->where(ImportRow::COL_STATUS, 'valid')->get();

            foreach ($rows as $row) {
                $data = $row->raw_data;

                Product::updateOrCreate(
                    ['codebar' => $data['codebar'] ?? null, 'store_id' => $storeId],
                    array_filter([
                        'name'          => $data['name'] ?? null,
                        'supplier_code' => $data['supplier_code'] ?? null,
                        'price_buy'     => isset($data['price_buy']) ? (float) $data['price_buy'] : null,
                        'price_sell_1'  => isset($data['price_sell_1']) ? (float) $data['price_sell_1'] : null,
                         'price'         => isset($data['price_sell_1']) ? (float) $data['price_sell_1'] : null,
                        'stock_alert'     => isset($data['stock_alert']) ? (int) $data['stock_alert'] : null,
                         'store_id'      => $storeId,
                        'user_id'       => auth()->id(),
                    ], fn($v) => $v !== null)
                );

                $row->update([ImportRow::COL_STATUS => 'committed']);
                $committed++;
            }
        });

        $import->update([
            Import::COL_STATUS         => Import::STATUS_COMMITTED,
            Import::COL_COMMITTED_ROWS => $committed,
        ]);

        return $import->fresh();
    }

    // ── private helpers ───────────────────────────────────────────────────────

    private function parseFile(string $path): array
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if ($ext === 'csv') {
            $csv = Reader::createFromPath($path, 'r');
            $csv->setHeaderOffset(0);
            return iterator_to_array($csv->getRecords(), false);
        }

        // XLSX support via OpenSpout (already in vendor)
        $reader = \OpenSpout\Reader\XLSX\Reader::class;
        if (class_exists($reader)) {
            return $this->parseXlsx($path);
        }

        throw new \RuntimeException('Unsupported file format: ' . $ext);
    }

    private function parseXlsx(string $path): array
    {
        $reader = new \OpenSpout\Reader\XLSX\Reader();
        $reader->open($path);
        $rows = [];
        $headers = [];
        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $i => $row) {
                $cells = array_map(fn($c) => $c->getValue(), $row->getCells());
                if ($i === 1) {
                    $headers = $cells;
                    continue;
                }
                $rows[] = array_combine($headers, $cells);
            }
            break; // first sheet only
        }
        $reader->close();
        return $rows;
    }

    /** @return string[] errors */
    private function validateRow(array $row): array
    {
        $errors = [];
        if (empty($row['name'])) {
            $errors[] = 'name is required';
        }
        if (!empty($row['price_buy']) && !is_numeric($row['price_buy'])) {
            $errors[] = 'price_buy must be numeric';
        }
        if (!empty($row['price_sell_1']) && !is_numeric($row['price_sell_1'])) {
            $errors[] = 'price_sell_1 must be numeric';
        }
        return $errors;
    }
}

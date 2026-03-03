<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\BaseController;
use App\Models\Ajustement;
use App\Models\Alert;
use App\Models\Brands;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Inventary;
use App\Models\Invoice;
use App\Models\OrderPurchase;
use App\Models\OrderSale;
use App\Models\Product;
use App\Models\Store;
use App\Models\Suppliers;
use App\Models\Transfert;
use App\Models\User;
use Illuminate\Http\Request;
use ZipArchive;

class ExportController extends BaseController
{
    /**
     * Export all store data for the authenticated owner
     */
    public function exportStoreData(Request $request)
    {
        $user = auth()->user();

        // Only owners can export data
        if (! $user->isOwner()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Only store owners can export data',
            ], 403);
        }

        $storeId = currentStoreId();
        $format = $request->input('format', 'csv'); // json or csv

        // Get request parameters
        $dataTypes = $request->input('data_types', [
            'store', 'products', 'customers', 'sales', 'purchases',
            'inventory', 'users', 'categories', 'brands', 'suppliers',
            'alerts', 'transfers', 'adjustments', 'invoices',
        ]);

        $dateFrom = $request->input('date_from') ? \Carbon\Carbon::parse($request->input('date_from')) : null;
        $dateTo = $request->input('date_to') ? \Carbon\Carbon::parse($request->input('date_to')) : null;

        if ($format === 'csv') {
            return $this->exportAsCsv($storeId, $dataTypes, $dateFrom, $dateTo);
        }

        // Default JSON export
        return $this->exportAsJson($storeId, $dataTypes, $dateFrom, $dateTo);
    }

    private function exportAsJson($storeId, $dataTypes, $dateFrom, $dateTo)
    {
        // Get selected data for the store
        $data = [];

        if (in_array('store', $dataTypes)) {
            $data['store'] = $this->getStoreData($storeId);
        }

        if (in_array('products', $dataTypes)) {
            $data['products'] = $this->getProductsData($storeId);
        }

        if (in_array('customers', $dataTypes)) {
            $data['customers'] = $this->getCustomersData($storeId);
        }

        if (in_array('sales', $dataTypes)) {
            $data['sales'] = $this->getSalesData($storeId, $dateFrom, $dateTo);
        }

        if (in_array('purchases', $dataTypes)) {
            $data['purchases'] = $this->getPurchasesData($storeId, $dateFrom, $dateTo);
        }

        if (in_array('inventory', $dataTypes)) {
            $data['inventory'] = $this->getInventoryData($storeId, $dateFrom, $dateTo);
        }

        if (in_array('users', $dataTypes)) {
            $data['users'] = $this->getUsersData($storeId);
        }

        if (in_array('categories', $dataTypes)) {
            $data['categories'] = $this->getCategoriesData($storeId);
        }

        if (in_array('brands', $dataTypes)) {
            $data['brands'] = $this->getBrandsData($storeId);
        }

        if (in_array('suppliers', $dataTypes)) {
            $data['suppliers'] = $this->getSuppliersData($storeId);
        }

        if (in_array('alerts', $dataTypes)) {
            $data['alerts'] = $this->getAlertsData($storeId, $dateFrom, $dateTo);
        }

        if (in_array('transfers', $dataTypes)) {
            $data['transfers'] = $this->getTransfersData($storeId, $dateFrom, $dateTo);
        }

        if (in_array('adjustments', $dataTypes)) {
            $data['adjustments'] = $this->getAdjustmentsData($storeId, $dateFrom, $dateTo);
        }

        if (in_array('invoices', $dataTypes)) {
            $data['invoices'] = $this->getInvoicesData($storeId, $dateFrom, $dateTo);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Store data exported successfully',
            'data' => $data,
            'exported_at' => now()->toISOString(),
            'store_id' => $storeId,
            'filters' => [
                'data_types' => $dataTypes,
                'date_from' => $dateFrom?->toISOString(),
                'date_to' => $dateTo?->toISOString(),
            ],
        ]);
    }

    private function exportAsCsv($storeId, $dataTypes, $dateFrom, $dateTo)
    {
        $zipFileName = 'store_export_'.$storeId.'_'.now()->format('Y_m_d_H_i_s').'.zip';
        $zipPath = storage_path('app/temp/'.$zipFileName);

        // Ensure temp directory exists
        $tempDir = storage_path('app/temp');
        if (! file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
            return response()->json(['error' => 'Could not create zip file'], 500);
        }

        // Generate CSV files for each data type
        if (in_array('store', $dataTypes)) {
            $this->addCsvToZip($zip, 'store.csv', $this->getStoreData($storeId), ['id', 'name', 'owner_id', 'created_at']);
        }

        if (in_array('products', $dataTypes)) {
            $this->addCsvToZip($zip, 'products.csv', $this->getProductsData($storeId),
                ['id', 'name', 'reference', 'codebar', 'price', 'stock_min', 'stock_max', 'category_id', 'brand_id', 'store_id', 'created_at']);
        }

        if (in_array('customers', $dataTypes)) {
            $this->addCsvToZip($zip, 'customers.csv', $this->getCustomersData($storeId),
                ['id', 'name', 'email', 'phone', 'adress', 'store_id', 'created_at']);
        }

        if (in_array('sales', $dataTypes)) {
            $this->addCsvToZip($zip, 'sales.csv', $this->getSalesData($storeId, $dateFrom, $dateTo),
                ['id', 'order_number', 'total_command', 'customer_id', 'user_id', 'store_id', 'status', 'created_at']);
        }

        if (in_array('purchases', $dataTypes)) {
            $this->addCsvToZip($zip, 'purchases.csv', $this->getPurchasesData($storeId, $dateFrom, $dateTo),
                ['id', 'order_number', 'reference', 'supplier_id', 'user_id', 'store_id', 'status', 'discount', 'created_at']);
        }

        if (in_array('inventory', $dataTypes)) {
            $this->addCsvToZip($zip, 'inventory.csv', $this->getInventoryData($storeId, $dateFrom, $dateTo),
                ['id', 'reference', 'status', 'store_id', 'created_at']);
        }

        if (in_array('users', $dataTypes)) {
            $this->addCsvToZip($zip, 'users.csv', $this->getUsersData($storeId),
                ['id', 'name', 'email', 'phone', 'role', 'statue', 'created_at']);
        }

        if (in_array('categories', $dataTypes)) {
            $this->addCsvToZip($zip, 'categories.csv', $this->getCategoriesData($storeId),
                ['id', 'name', 'description', 'store_id', 'created_at']);
        }

        if (in_array('brands', $dataTypes)) {
            $this->addCsvToZip($zip, 'brands.csv', $this->getBrandsData($storeId),
                ['id', 'name', 'description', 'store_id', 'created_at']);
        }

        if (in_array('suppliers', $dataTypes)) {
            $this->addCsvToZip($zip, 'suppliers.csv', $this->getSuppliersData($storeId),
                ['id', 'first_name', 'last_name', 'company_name', 'email', 'phone', 'address', 'store_id', 'created_at']);
        }

        if (in_array('alerts', $dataTypes)) {
            $this->addCsvToZip($zip, 'alerts.csv', $this->getAlertsData($storeId, $dateFrom, $dateTo),
                ['id', 'title', 'message', 'type', 'severity', 'is_read', 'is_resolved', 'store_id', 'created_at']);
        }

        if (in_array('transfers', $dataTypes)) {
            $this->addCsvToZip($zip, 'transfers.csv', $this->getTransfersData($storeId, $dateFrom, $dateTo),
                ['id', 'reference', 'source_store_id', 'target_store_id', 'status', 'created_by', 'created_at']);
        }

        if (in_array('adjustments', $dataTypes)) {
            $this->addCsvToZip($zip, 'adjustments.csv', $this->getAdjustmentsData($storeId, $dateFrom, $dateTo),
                ['id', 'reference', 'reason', 'status', 'store_id', 'user_id', 'created_at']);
        }

        if (in_array('invoices', $dataTypes)) {
            $this->addCsvToZip($zip, 'invoices.csv', $this->getInvoicesData($storeId, $dateFrom, $dateTo),
                ['id', 'customer_id', 'total', 'discount', 'status', 'store_id', 'created_at']);
        }

        $zip->close();

        return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
    }

    private function addCsvToZip($zip, $filename, $data, $headers)
    {
        if ($data instanceof \Illuminate\Database\Eloquent\Collection) {
            $data = $data->toArray();
        } elseif ($data instanceof \Illuminate\Database\Eloquent\Model) {
            $data = [$data->toArray()];
        } elseif (! is_array($data)) {
            $data = [$data];
        }

        $csvContent = $this->arrayToCsv($data, $headers);
        $zip->addFromString($filename, $csvContent);
    }

    private function arrayToCsv($data, $headers)
    {
        $output = fopen('php://temp', 'r+');

        // Write headers
        fputcsv($output, $headers);

        // Write data
        foreach ($data as $row) {
            $csvRow = [];
            foreach ($headers as $header) {
                $value = $row[$header] ?? '';
                
                // Handle arrays and objects by converting to JSON
                if (is_array($value) || is_object($value)) {
                    $value = json_encode($value);
                }
                
                $csvRow[] = $value;
            }
            fputcsv($output, $csvRow);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    private function getStoreData($storeId)
    {
        return Store::with(['owner'])->find($storeId);
    }

    private function getProductsData($storeId)
    {
        return Product::where('store_id', $storeId)
            ->with(['category', 'brand'])
            ->get();
    }

    private function getCustomersData($storeId)
    {
        return Customer::where('store_id', $storeId)->get();
    }

    private function getSalesData($storeId, $dateFrom = null, $dateTo = null)
    {
        $query = OrderSale::where('store_id', $storeId)
            ->with(['customer', 'user', 'orderItems.product']);

        if ($dateFrom) {
            $query->where('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->where('created_at', '<=', $dateTo);
        }

        return $query->get();
    }

    private function getPurchasesData($storeId, $dateFrom = null, $dateTo = null)
    {
        $query = OrderPurchase::where('store_id', $storeId)
            ->with(['supplier', 'user', 'orderItems.product']);

        if ($dateFrom) {
            $query->where('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->where('created_at', '<=', $dateTo);
        }

        return $query->get();
    }

    private function getInventoryData($storeId, $dateFrom = null, $dateTo = null)
    {
        $query = Inventary::where('store_id', $storeId)
            ->with(['items.product']);

        if ($dateFrom) {
            $query->where('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->where('created_at', '<=', $dateTo);
        }

        return $query->get();
    }

    private function getUsersData($storeId)
    {
        return User::whereHas('workingStores', function ($query) use ($storeId) {
            $query->where('store_id', $storeId);
        })->with(['roles' => function ($query) use ($storeId) {
            $query->where('store_id', $storeId);
        }])->get();
    }

    private function getCategoriesData($storeId)
    {
        return Category::where('store_id', $storeId)->get();
    }

    private function getBrandsData($storeId)
    {
        return Brands::where('store_id', $storeId)->get();
    }

    private function getSuppliersData($storeId)
    {
        return Suppliers::where('store_id', $storeId)->get();
    }

    private function getAlertsData($storeId, $dateFrom = null, $dateTo = null)
    {
        $query = Alert::where('store_id', $storeId);

        if ($dateFrom) {
            $query->where('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->where('created_at', '<=', $dateTo);
        }

        return $query->get();
    }

    private function getTransfersData($storeId, $dateFrom = null, $dateTo = null)
    {
        $query = Transfert::where(function ($query) use ($storeId) {
            $query->where('source_store_id', $storeId)
                ->orWhere('target_store_id', $storeId);
        })->with(['sourceStore', 'targetStore', 'createdBy', 'items.product']);

        if ($dateFrom) {
            $query->where('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->where('created_at', '<=', $dateTo);
        }

        return $query->get();
    }

    private function getAdjustmentsData($storeId, $dateFrom = null, $dateTo = null)
    {
        $query = Ajustement::where('store_id', $storeId)
            ->with(['user', 'items.product']);

        if ($dateFrom) {
            $query->where('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->where('created_at', '<=', $dateTo);
        }

        return $query->get();
    }

    private function getInvoicesData($storeId, $dateFrom = null, $dateTo = null)
    {
        $query = Invoice::where('store_id', $storeId)
            ->with(['customer']);

        if ($dateFrom) {
            $query->where('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->where('created_at', '<=', $dateTo);
        }

        return $query->get();
    }
}

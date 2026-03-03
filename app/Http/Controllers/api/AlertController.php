<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\BaseController;
use App\Models\Alert;
use App\Services\Alert\AlertService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AlertController extends BaseController
{
    private AlertService $alertService;

    public function __construct(AlertService $alertService)
    {
        parent::__construct();
        $this->alertService = $alertService;
    }

    /**
     * Display a listing of alerts
     */
    public function index(Request $request)
    {
        $storeId = $this->storeId();

        if (!$storeId) {
            return response()->json(['error' => 'No store found'], 404);
        }

        // Build filters from request
        $filters = [];
        if ($request->has('type')) {
            $filters['type'] = $request->input('type');
        }
        if ($request->has('category')) {
            $filters['category'] = $request->input('category');
        }
        if ($request->has('severity')) {
            $filters['severity'] = $request->input('severity');
        }
        if ($request->has('is_read')) {
            $filters['is_read'] = $request->boolean('is_read');
        }
        if ($request->has('is_resolved')) {
            $filters['is_resolved'] = $request->boolean('is_resolved');
        }

        $alerts = $this->alertService->getAlerts($storeId, $filters);

        return response()->json($alerts, Response::HTTP_OK);
    }

    /**
     * Get alert statistics
     */
    public function stats(Request $request)
    {
        $storeId = $this->storeId();

        if (!$storeId) {
            return response()->json(['error' => 'No store found'], 404);
        }

        $stats = $this->alertService->getAlertStats($storeId);

        return response()->json($stats, Response::HTTP_OK);
    }

    /**
     * Display the specified alert
     */
    public function show(Alert $alert)
    {
        // Check if alert belongs to current store
        if ($alert->store_id && $alert->store_id !== $this->storeId()) {
            return response()->json(['error' => 'Alert not found'], 404);
        }

        return response()->json($alert->load(['customer', 'product', 'store', 'user', 'resolvedBy']), Response::HTTP_OK);
    }

    /**
     * Mark alert as read
     */
    public function markAsRead(Alert $alert)
    {
        // Check if alert belongs to current store
        if ($alert->store_id && $alert->store_id !== $this->storeId()) {
            return response()->json(['error' => 'Alert not found'], 404);
        }

        if ($this->alertService->markAsRead($alert->id)) {
            return response()->json([
                'message' => 'Alert marked as read',
                'alert' => $alert->fresh()
            ], Response::HTTP_OK);
        }

        return response()->json(['error' => 'Failed to mark alert as read'], 500);
    }

    /**
     * Mark alert as resolved
     */
    public function markAsResolved(Alert $alert)
    {
        // Check if alert belongs to current store
        if ($alert->store_id && $alert->store_id !== $this->storeId()) {
            return response()->json(['error' => 'Alert not found'], 404);
        }

        if ($this->alertService->markAsResolved($alert->id, auth()->id())) {
            return response()->json([
                'message' => 'Alert marked as resolved',
                'alert' => $alert->fresh()->load('resolvedBy')
            ], Response::HTTP_OK);
        }

        return response()->json(['error' => 'Failed to mark alert as resolved'], 500);
    }

    /**
     * Generate alerts manually
     */
    public function generate(Request $request)
    {
        $storeId = $this->storeId();

        if (!$storeId) {
            return response()->json(['error' => 'No store found'], 404);
        }

        $type = $request->input('type', 'all'); // customer, product, all

        try {
            $results = [];

            switch ($type) {
                case 'customer':
                    $results['customer_alerts'] = $this->alertService->generateCustomerAlerts($storeId);
                    break;
                case 'product':
                    $results['product_alerts'] = $this->alertService->generateProductStockAlerts($storeId);
                    break;
                case 'all':
                default:
                    $results = $this->alertService->generateAllAlerts($storeId);
                    break;
            }

            return response()->json([
                'message' => 'Alerts generated successfully',
                'results' => $results
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to generate alerts',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk mark alerts as read
     */
    public function bulkMarkAsRead(Request $request)
    {
        $storeId = $this->storeId();

        if (!$storeId) {
            return response()->json(['error' => 'No store found'], 404);
        }

        $validated = $request->validate([
            'alert_ids' => 'required|array',
            'alert_ids.*' => 'integer|exists:alerts,id'
        ]);

        $count = 0;
        foreach ($validated['alert_ids'] as $alertId) {
            $alert = Alert::find($alertId);
            if ($alert && $alert->store_id === $storeId) {
                $alert->markAsRead();
                $count++;
            }
        }

        return response()->json([
            'message' => "{$count} alerts marked as read"
        ], Response::HTTP_OK);
    }

    /**
     * Bulk mark alerts as resolved
     */
    public function bulkMarkAsResolved(Request $request)
    {
        $storeId = $this->storeId();

        if (!$storeId) {
            return response()->json(['error' => 'No store found'], 404);
        }

        $validated = $request->validate([
            'alert_ids' => 'required|array',
            'alert_ids.*' => 'integer|exists:alerts,id'
        ]);

        $count = 0;
        foreach ($validated['alert_ids'] as $alertId) {
            $alert = Alert::find($alertId);
            if ($alert && $alert->store_id === $storeId) {
                $alert->markAsResolved(auth()->user());
                $count++;
            }
        }

        return response()->json([
            'message' => "{$count} alerts marked as resolved"
        ], Response::HTTP_OK);
    }
}

<?php

namespace App\Console\Commands;

use App\Services\Alert\AlertService;
use Illuminate\Console\Command;

class GenerateAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'alerts:generate {--store= : Generate alerts for specific store ID} {--type= : Generate specific alert type (customer, product, all)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate system alerts for customers and products';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $alertService = app(AlertService::class);
        $storeId = $this->option('store') ? (int) $this->option('store') : null;
        $type = $this->option('type') ?: 'all';

        $this->info('Starting alert generation...');

        if ($storeId) {
            $this->info("Generating alerts for store ID: {$storeId}");
        } else {
            $this->info('Generating alerts for all stores');
        }

        $results = [];

        switch ($type) {
            case 'customer':
                $this->info('Generating customer alerts...');
                $results['customer_alerts'] = $alertService->generateCustomerAlerts($storeId);
                break;

            case 'product':
                $this->info('Generating product stock alerts...');
                $results['product_alerts'] = $alertService->generateProductStockAlerts($storeId);
                break;

            case 'all':
            default:
                $this->info('Generating all alerts...');
                $results = $alertService->generateAllAlerts($storeId);
                break;
        }

        // Display results
        $this->newLine();
        $this->info('Alert Generation Results:');
        $this->table(
            ['Alert Type', 'Count'],
            [
                ['Customer Alerts', $results['customer_alerts'] ?? 0],
                ['Product Alerts', $results['product_alerts'] ?? 0],
                ['Total', $results['total'] ?? array_sum($results)],
            ]
        );

        $this->newLine();
        $this->info('Alert generation completed successfully!');

        return Command::SUCCESS;
    }
}

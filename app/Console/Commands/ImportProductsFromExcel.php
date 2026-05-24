<?php

namespace App\Console\Commands;

use Database\Seeders\ProductsFromExcelSeeder;
use Illuminate\Console\Command;

class ImportProductsFromExcel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:import-excel 
                            {file? : Path to the Excel file (optional, defaults to database/import/la_base_de_donnes_de_nouveau_systemes_18-05.xlsx)}
                            {--preview : Show preview without importing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import products and create purchase orders from Excel file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('╔═══════════════════════════════════════════════════════════════╗');
        $this->info('║           Import Products from Excel                          ║');
        $this->info('╚═══════════════════════════════════════════════════════════════╝');
        $this->newLine();

        $file = $this->argument('file') ?? database_path('import/database.xlsx');

        if (!file_exists($file)) {
            $this->error("❌ Excel file not found: {$file}");
            $this->newLine();
            $this->info('Please place your Excel file at:');
            $this->info('  database/import/database.xlsx');
            $this->newLine();
            return Command::FAILURE;
        }

        $this->info("📄 File: " . basename($file));
        $this->info("📏 Size: " . number_format(filesize($file) / 1024, 2) . " KB");
        $this->newLine();

        if ($this->option('preview')) {
            $this->info('🔍 Preview mode - No data will be imported');
            $this->newLine();

            // Run preview script
            passthru('php ' . database_path('seeders/PreviewExcelImport.php'));

            return Command::SUCCESS;
        }

        // Confirm before proceeding
        if (!$this->confirm('This will import all products and create purchase orders. Continue?', true)) {
            $this->warn('Import cancelled.');
            return Command::CANCELLED;
        }

        $this->newLine();
        $this->info('🚀 Starting import...');
        $this->newLine();

        // Run the seeder
        $seeder = new ProductsFromExcelSeeder();
        $seeder->setCommand($this);
        $seeder->run();

        return Command::SUCCESS;
    }
}

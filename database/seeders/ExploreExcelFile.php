#!/usr/bin/env php
<?php

require __DIR__ . '/../../vendor/autoload.php';

use OpenSpout\Reader\XLSX\Reader;

$excelFile = __DIR__ . '/../import/database.xlsx';

if (!file_exists($excelFile)) {
    echo "Excel file not found: $excelFile\n";
    exit(1);
}

echo "Reading Excel file: $excelFile\n\n";

$reader = new Reader();
$reader->open($excelFile);

$sheetIndex = 0;
foreach ($reader->getSheetIterator() as $sheet) {
    $sheetIndex++;
    echo "========================================\n";
    echo "Sheet #{$sheetIndex}: " . $sheet->getName() . "\n";
    echo "========================================\n\n";

    $rowIndex = 0;
    foreach ($sheet->getRowIterator() as $row) {
        $rowIndex++;
        $cells = $row->getCells();
        $data = array_map(fn($cell) => $cell->getValue(), $cells);

        // Print first 5 rows of each sheet
        if ($rowIndex <= 5) {
            echo "Row $rowIndex: " . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n";
        }

        // Stop after showing first 5 rows
        if ($rowIndex == 5) {
            echo "\n... (showing first 5 rows only)\n\n";
            break;
        }
    }
}

$reader->close();

echo "Done!\n";

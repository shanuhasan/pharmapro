<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

try {
    $filePath = 'C:\\Users\\insph\\Downloads\\SB-26-232615.xls';
    $spreadsheet = IOFactory::load($filePath);
    $data = $spreadsheet->getActiveSheet()->toArray();
    
    // Dump the last 20 rows to see if there's a grand total
    $totalRows = count($data);
    $startRow = max(0, $totalRows - 20);
    echo "Last 20 rows:\n";
    for($i = $startRow; $i < $totalRows; $i++) {
        print_r($data[$i]);
    }
    
    $grandAmount = 0;
    foreach($data as $k => $row) {
        if ($k > 0) {
            $grandAmount += (float)($row[20] ?? 0);
        }
    }
    echo "Grand Sum of Amount (All rows): " . $grandAmount . "\n";
} catch (Exception $e) {
    echo $e->getMessage();
}

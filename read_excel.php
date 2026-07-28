<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

try {
    $filePath = 'C:\\Users\\insph\\Downloads\\SB-26-198770.xls';
    $spreadsheet = IOFactory::load($filePath);
    $data = $spreadsheet->getActiveSheet()->toArray();
    
    echo "First 5 rows:\n";
    for($i = 0; $i < min(5, count($data)); $i++) {
        print_r($data[$i]);
    }
} catch (Exception $e) {
    echo $e->getMessage();
}

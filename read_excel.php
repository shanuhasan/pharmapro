<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

try {
    $filePath = 'C:\\Users\\insph\\Downloads\\SB-26-232615.xls';
    $spreadsheet = IOFactory::load($filePath);
    $data = $spreadsheet->getActiveSheet()->toArray();
    print_r($data[0]);
} catch (Exception $e) {
    echo $e->getMessage();
}

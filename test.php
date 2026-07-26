<?php
require 'vendor/autoload.php';
$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load("C:\\Users\\insph\\Downloads\\SB-26-232615.xls");
$worksheet = $spreadsheet->getActiveSheet();
$rows = $worksheet->toArray();
print_r($rows[0]);
print_r($rows[1]);

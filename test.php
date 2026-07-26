<?php
require 'vendor/autoload.php';
$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load("C:\\Users\\insph\\Downloads\\20442075273_DSPTI375610_with_header.csv");
$rows = $spreadsheet->getActiveSheet()->toArray();
foreach($rows as $r) {
    if (isset($r[5]) && preg_match('/Platform Fees|COD Charges/i', $r[5])) {
        print_r($r);
    }
}

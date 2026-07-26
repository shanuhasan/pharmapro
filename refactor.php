<?php
$content = file_get_contents('d:\xampp\htdocs\pharmapro\app\Http\Controllers\PurchaseController.php');

$startMarker = 'foreach ($rows as $index => $row) {';
$endMarker = '$supplierId = null;';

$startPos = strpos($content, $startMarker);
$endPos = strpos($content, $endMarker, $startPos);

$newLoop = <<<'PHP'
foreach ($rows as $index => $row) {
                if ($index === 0) continue; // Skip header row
                
                $parsedData = null;
                if ($isAwacsFormat) {
                    $parsedData = $this->parseAwacsRow($row, $supplierName, $invoiceNumber, $purchaseDate);
                } else {
                    $parsedData = $this->parseStandardRow($row, $supplierName, $invoiceNumber, $purchaseDate);
                }

                if (!$parsedData) continue;

                $items[] = $this->processParsedRow($parsedData, $supplierName);
            }

            
PHP;

$beforeLoop = substr($content, 0, $startPos);
$afterLoop = substr($content, $endPos);

$controllerClassEnd = strrpos($afterLoop, '}');

$newFunctions = <<<'PHP'
    private function parseAwacsRow($row, &$supplierName, &$invoiceNumber, &$purchaseDate)
    {
        if (empty($row[5])) return null;
        
        if (!$invoiceNumber) {
            $supplierName = $row[2] ?? null;
            $invoiceNumber = 'AWACS-' . time();
            $purchaseDate = date('Y-m-d');
        }
        
        $medicineName = $row[5];
        $packStr = $row[6] ?? '';
        $batchStr = $row[8] ?? null;
        $expiryStr = $row[9] ?? null;
        $qtyStr = $row[15] ?? 0;
        $fqtyStr = $row[16] ?? 0;
        $srateStr = $row[11] ?? 0;
        $mrpStr = $row[12] ?? 0;
        $disStr = $row[17] ?? 0;
        $halfpStr = 0;
        $cgstStr = $row[22] ?? 0;
        $sgstStr = $row[26] ?? 0;
        $hsnStr = $row[30] ?? null;
        
        $medicinesPerStrip = 1;
        if (preg_match('/\(?(\d+)\s*(Tab|Cap|Capsule|Tablet)s?\)?/i', $medicineName, $m)) {
            $medicinesPerStrip = (int)$m[1];
            if ($medicinesPerStrip <= 0) $medicinesPerStrip = 1;
        }
        
        $formattedExpiry = null;
        if ($expiryStr && strlen(trim($expiryStr)) >= 6) {
            try {
                $formattedExpiry = \Carbon\Carbon::createFromFormat('dmY', trim($expiryStr))->endOfMonth()->format('Y-m-d');
            } catch (\Exception $e) {
                $formattedExpiry = null;
            }
        }

        return compact(
            'medicineName', 'packStr', 'batchStr', 'formattedExpiry', 'qtyStr', 'fqtyStr', 
            'srateStr', 'mrpStr', 'disStr', 'halfpStr', 'cgstStr', 'sgstStr', 'hsnStr', 'medicinesPerStrip'
        );
    }

    private function parseStandardRow($row, &$supplierName, &$invoiceNumber, &$purchaseDate)
    {
        if (empty($row[6]) || empty($row[1])) return null;
        
        if (!$invoiceNumber) {
            $supplierName = $row[0] ?? null;
            $invoiceNumber = $row[1] ?? null;
            
            $rawDate = $row[2] ?? null;
            if ($rawDate) {
                try {
                    $purchaseDate = \Carbon\Carbon::createFromFormat('d/m/Y', $rawDate)->format('Y-m-d');
                } catch (\Exception $e) {
                    try {
                        $purchaseDate = \Carbon\Carbon::parse(str_replace('/', '-', $rawDate))->format('Y-m-d');
                    } catch (\Exception $e) {
                        $purchaseDate = null;
                    }
                }
            }
        }

        $medicineName = $row[6];
        $packStr = $row[7] ?? '';
        $batchStr = $row[8] ?? null;
        $expiryStr = $row[9] ?? null;
        $qtyStr = $row[10] ?? 0;
        $fqtyStr = $row[11] ?? 0;
        $srateStr = $row[14] ?? 0;
        $mrpStr = $row[15] ?? 0;
        $disStr = $row[16] ?? 0;
        $halfpStr = $row[12] ?? 0;
        $cgstStr = $row[26] ?? 0;
        $sgstStr = $row[27] ?? 0;
        $hsnStr = $row[25] ?? null;

        $medicinesPerStrip = 1;
        if (preg_match('/^(\d+)\s*[`\'’]?\s*S$/i', trim($packStr), $matches)) {
            $medicinesPerStrip = (int)$matches[1];
            if ($medicinesPerStrip <= 0) $medicinesPerStrip = 1;
        }
        
        $formattedExpiry = null;
        if ($expiryStr) {
            try {
                $formattedExpiry = \Carbon\Carbon::createFromFormat('m/y', $expiryStr)->endOfMonth()->format('Y-m-d');
            } catch (\Exception $e) {
                $formattedExpiry = null;
            }
        }

        return compact(
            'medicineName', 'packStr', 'batchStr', 'formattedExpiry', 'qtyStr', 'fqtyStr', 
            'srateStr', 'mrpStr', 'disStr', 'halfpStr', 'cgstStr', 'sgstStr', 'hsnStr', 'medicinesPerStrip'
        );
    }

    private function processParsedRow($parsedData, $supplierName)
    {
        extract($parsedData);

        $cleanName = preg_replace('/[*#]$/', '', trim($medicineName));
        $medicine = Medicine::where('name', 'LIKE', '%' . $cleanName . '%')->first();

        if (!$medicine) {
            $defaultCategory = \App\Models\MedicineCategory::firstOrCreate(['name' => 'General']);
            $defaultUnit = \App\Models\Unit::firstOrCreate(['name' => 'Strip', 'abbreviation' => 'Str']);
            
            $pharmacyId = auth()->user()->pharmacy_id;
            if (!$pharmacyId) {
                $branch = \App\Models\Branch::first();
                if ($branch) $pharmacyId = $branch->pharmacy_id;
            }

            $medicine = Medicine::create([
                'name' => $cleanName,
                'medicines_per_strip' => $medicinesPerStrip,
                'hsn_code' => $hsnStr,
                'category_id' => $defaultCategory->id,
                'unit_id' => $defaultUnit->id,
                'manufacturer' => $supplierName ?? null,
                'is_active' => true,
                'pharmacy_id' => $pharmacyId
            ]);
        } else {
            if ($medicinesPerStrip > 1 && $medicine->medicines_per_strip != $medicinesPerStrip) {
                $medicine->update(['medicines_per_strip' => $medicinesPerStrip]);
            }
        }

        $qty = (float)$qtyStr;
        $fqty = (float)$fqtyStr;
        $srate = (float)$srateStr;
        $dis = (float)$disStr;
        $halfp = (float)$halfpStr;
        
        $totalQty = $qty + $fqty;
        
        $grossTotal = $qty * $srate;
        $discountAmount = $grossTotal * ($dis / 100);
        $halfpAmount = $grossTotal * ($halfp / 100);
        $rowTotal = $grossTotal - $discountAmount - $halfpAmount;
        
        $cgstPercent = (float)$cgstStr;
        $sgstPercent = (float)$sgstStr;
        $taxPercent = $cgstPercent + $sgstPercent;
        
        $taxAmount = $rowTotal * ($taxPercent / 100);
        $rowTotal += $taxAmount;
        $purchasePrice = $srate;

        return [
            'medicine_name' => $medicineName,
            'medicine_id' => $medicine ? $medicine->id : null,
            'medicines_per_strip' => $medicinesPerStrip,
            'hsn_code' => $hsnStr ?? ($medicine ? $medicine->hsn_code : ''),
            'batch_number' => $batchStr,
            'expiry_date' => $formattedExpiry,
            'quantity' => $totalQty,
            'purchase_price' => $purchasePrice,
            'sale_price' => (float)$mrpStr,
            'item_total' => $rowTotal,
        ];
    }
}
PHP;

$finalAfterLoop = substr($afterLoop, 0, $controllerClassEnd) . "\n" . $newFunctions;
$finalContent = $beforeLoop . $newLoop . $finalAfterLoop;
file_put_contents('d:\xampp\htdocs\pharmapro\app\Http\Controllers\PurchaseController.php', $finalContent);
echo "done";

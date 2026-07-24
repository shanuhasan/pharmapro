<?php
$directories = ['app', 'resources/views'];
$searchPattern = '/\$\{\{\s*number_format\(/';
$replacement = "{{ setting('currency_symbol', '₹') }}{{ number_format(";

function processDirRegex($dir, $pattern, $replacement) {
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $path = $dir . DIRECTORY_SEPARATOR . $file;
        if (is_dir($path)) {
            processDirRegex($path, $pattern, $replacement);
        } else if (pathinfo($path, PATHINFO_EXTENSION) === 'php') {
            $content = file_get_contents($path);
            if (preg_match($pattern, $content)) {
                $newContent = preg_replace($pattern, $replacement, $content);
                file_put_contents($path, $newContent);
                echo "Updated regex: $path\n";
            }
        }
    }
}

foreach ($directories as $dir) {
    processDirRegex(__DIR__ . DIRECTORY_SEPARATOR . $dir, $searchPattern, $replacement);
}

echo "Done regex.\n";

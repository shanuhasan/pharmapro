<?php
$directories = ['app', 'resources/views'];
$search = "setting('currency_symbol', '$')";
$replace = "setting('currency_symbol', '₹')";

function processDir($dir, $search, $replace) {
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $path = $dir . DIRECTORY_SEPARATOR . $file;
        if (is_dir($path)) {
            processDir($path, $search, $replace);
        } else if (pathinfo($path, PATHINFO_EXTENSION) === 'php') {
            $content = file_get_contents($path);
            if (strpos($content, $search) !== false) {
                $newContent = str_replace($search, $replace, $content);
                file_put_contents($path, $newContent);
                echo "Updated: $path\n";
            }
        }
    }
}

foreach ($directories as $dir) {
    processDir(__DIR__ . DIRECTORY_SEPARATOR . $dir, $search, $replace);
}

echo "Done.\n";

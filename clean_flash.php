<?php
$dir = new RecursiveDirectoryIterator(__DIR__ . '/resources/views');
$ite = new RecursiveIteratorIterator($dir);

foreach ($ite as $file) {
    if ($file->isFile() && $file->getExtension() === 'php' && strpos($file->getPathname(), 'layouts') === false) {
        $content = file_get_contents($file->getPathname());
        $original = $content;
        
        $patternSuccess = '/[ \t]*@if\(\s*session\(\'success\'\)\s*\)\s*<div[^>]*role="alert"[^>]*>.*?<\/div>\s*@endif/is';
        $content = preg_replace($patternSuccess, '', $content);
        
        $patternError = '/[ \t]*@if\(\s*session\(\'error\'\)\s*\)\s*<div[^>]*role="alert"[^>]*>.*?<\/div>\s*@endif/is';
        $content = preg_replace($patternError, '', $content);
        
        if ($content !== $original) {
            file_put_contents($file->getPathname(), $content);
            echo "Updated: " . $file->getPathname() . "\n";
        }
    }
}
echo "Done!\n";

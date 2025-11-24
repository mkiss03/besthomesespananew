<?php
/**
 * Property Detail Page Wrapper
 * Forwards requests to public/property.php
 */

$publicPropertyFile = __DIR__ . '/public/property.php';

if (file_exists($publicPropertyFile)) {
    require $publicPropertyFile;
    exit;
}

http_response_code(500);
echo "Hiba: a public/property.php nem található.";

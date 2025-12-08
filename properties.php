<?php
/**
 * Properties Page Wrapper
 * Forwards requests to public/properties.php
 */

$publicPropertiesFile = __DIR__ . '/public/properties.php';

if (file_exists($publicPropertiesFile)) {
    require $publicPropertiesFile;
    exit;
}

http_response_code(500);
echo "Hiba: a public/properties.php nem található.";

<?php
// Gyökér index: továbbdob a public/index.php-re

$publicIndex = __DIR__ . '/public/index.php';

if (file_exists($publicIndex)) {
    require $publicIndex;
    exit;
}

http_response_code(500);
echo "Hiba: a public/index.php nem található.";

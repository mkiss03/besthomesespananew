<?php
// Test cURL connection
$testUrl = 'https://www.tmgrupoinmobiliario.com/en/properties/U68-04_beach-apartments-sea-views-spain-benidorm-costa-blanca-north-tm-tower-by-tm';

echo "Testing cURL to TM Grupo...\n";
echo "URL: $testUrl\n\n";

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $testUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_MAXREDIRS => 5,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_SSL_VERIFYPEER => false, // Try without SSL verification
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
    CURLOPT_VERBOSE => true,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
$errno = curl_errno($ch);

echo "HTTP Code: $httpCode\n";
echo "cURL Error Number: $errno\n";
echo "cURL Error: $error\n";
echo "Response Length: " . strlen($response) . " bytes\n";

if ($response && $httpCode === 200) {
    echo "\n✓ SUCCESS: Connection works!\n";
    echo "First 200 chars:\n";
    echo substr($response, 0, 200) . "...\n";
} else {
    echo "\n✗ FAILED: Cannot download\n";
}

curl_close($ch);

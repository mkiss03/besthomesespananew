<?php
/**
 * TM Grupo Inmobiliario New Build Properties Importer
 *
 * Importer for TM Grupo listings – used with explicit written permission for these URLs.
 *
 * This is a ONE-TIME importer that pulls new-build developments from TM Grupo Inmobiliario
 * and inserts them into our database with images.
 *
 * Usage: php tools/import_tm_new_builds.php
 */

require_once __DIR__ . '/../config/config.php';

// URLs with explicit permission to import
const TM_LISTINGS = [
    'https://www.tmgrupoinmobiliario.com/en/properties/U68-04_beach-apartments-sea-views-spain-benidorm-costa-blanca-north-tm-tower-by-tm',
    'https://www.tmgrupoinmobiliario.com/es/promociones-inmobiliarias/U68-03_apartamentos-benidorm-primera-linea-playa-poniente-sunset-sailors-by-tm',
    'https://www.tmgrupoinmobiliario.com/es/promociones-inmobiliarias/U92-01_apartamentos-alicante-calpe-playa-de-la-fossa-azure-icons-by-tm',
    'https://www.tmgrupoinmobiliario.com/es/promociones-inmobiliarias/U94-01_vivienda-unifamiliar-adosado-apartamento-atico-obra-nueva-playa-el-puig-valencia-santa-maria-sea',
    'https://www.tmgrupoinmobiliario.com/es/promociones-inmobiliarias/U17-07_apartamentos-playa-almeria-mar-de-pulpi-7',
];

// City mapping for URLs (as fallback if parsing fails)
const CITY_MAPPING = [
    'benidorm' => ['city' => 'Benidorm', 'region' => 'Costa Blanca', 'province' => 'Alicante'],
    'calpe' => ['city' => 'Calpe', 'region' => 'Costa Blanca', 'province' => 'Alicante'],
    'el-puig' => ['city' => 'El Puig', 'region' => 'Valencia Region', 'province' => 'Valencia'],
    'valencia' => ['city' => 'El Puig', 'region' => 'Valencia Region', 'province' => 'Valencia'],
    'mar-de-pulpi' => ['city' => 'Mar de Pulpí', 'region' => 'Costa de Almería', 'province' => 'Almería'],
    'almeria' => ['city' => 'Mar de Pulpí', 'region' => 'Costa de Almería', 'province' => 'Almería'],
];

echo "========================================\n";
echo "TM Grupo New Builds Importer\n";
echo "========================================\n\n";

// Initialize database connection
try {
    $pdo = getPDO();
    echo "✓ Database connected\n\n";
} catch (Exception $e) {
    die("✗ Database connection failed: " . $e->getMessage() . "\n");
}

// Ensure New Build property type exists
try {
    $stmt = $pdo->query("SELECT id FROM property_types WHERE name = 'New Build'");
    $newBuildType = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$newBuildType) {
        echo "→ Creating 'New Build' property type...\n";
        $stmt = $pdo->prepare("INSERT INTO property_types (name, name_hu) VALUES (?, ?)");
        $stmt->execute(['New Build', 'Új építésű']);
        $newBuildTypeId = $pdo->lastInsertId();
        echo "✓ Created New Build type (ID: $newBuildTypeId)\n\n";
    } else {
        $newBuildTypeId = $newBuildType['id'];
        echo "✓ New Build type exists (ID: $newBuildTypeId)\n\n";
    }
} catch (PDOException $e) {
    die("✗ Error setting up property type: " . $e->getMessage() . "\n");
}

// Ensure upload directory exists
if (!is_dir(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0755, true);
    echo "✓ Created upload directory: " . UPLOAD_PATH . "\n\n";
}

// Process each listing
$totalImported = 0;
$totalSkipped = 0;

foreach (TM_LISTINGS as $index => $url) {
    $listingNum = $index + 1;
    echo "[$listingNum/" . count(TM_LISTINGS) . "] Processing: $url\n";
    echo str_repeat('-', 80) . "\n";

    try {
        // Download HTML
        $html = downloadUrl($url);
        if (!$html) {
            echo "✗ Failed to download HTML\n\n";
            $totalSkipped++;
            continue;
        }
        echo "✓ Downloaded HTML (" . strlen($html) . " bytes)\n";

        // Parse property data
        $propertyData = parsePropertyPage($html, $url);
        if (!$propertyData) {
            echo "✗ Failed to parse property data\n\n";
            $totalSkipped++;
            continue;
        }
        echo "✓ Parsed property data: {$propertyData['title']}\n";

        // Get or create location
        $locationId = getOrCreateLocation($pdo, $propertyData['location']);
        if (!$locationId) {
            echo "✗ Failed to get/create location\n\n";
            $totalSkipped++;
            continue;
        }
        echo "✓ Location ID: $locationId ({$propertyData['location']['city']})\n";

        // Check if property already exists (by slug)
        $slug = generateSlug($propertyData['title']);
        $stmt = $pdo->prepare("SELECT id FROM properties WHERE slug = ?");
        $stmt->execute([$slug]);
        if ($stmt->fetch()) {
            echo "⚠ Property already exists with slug: $slug (skipping)\n\n";
            $totalSkipped++;
            continue;
        }

        // Download images
        $downloadedImages = downloadImages($propertyData['images'], $slug);
        echo "✓ Downloaded " . count($downloadedImages) . " images\n";

        // Insert property into database
        $propertyId = insertProperty($pdo, $propertyData, $newBuildTypeId, $locationId, $slug);
        if (!$propertyId) {
            echo "✗ Failed to insert property\n\n";
            $totalSkipped++;
            continue;
        }
        echo "✓ Inserted property (ID: $propertyId)\n";

        // Insert images
        $imageCount = insertImages($pdo, $propertyId, $downloadedImages);
        echo "✓ Inserted $imageCount image records\n";

        echo "✓ SUCCESS: Property imported!\n\n";
        $totalImported++;

    } catch (Exception $e) {
        echo "✗ Error: " . $e->getMessage() . "\n\n";
        $totalSkipped++;
    }
}

// Summary
echo "========================================\n";
echo "Import Complete\n";
echo "========================================\n";
echo "Total processed: " . count(TM_LISTINGS) . "\n";
echo "Successfully imported: $totalImported\n";
echo "Skipped/failed: $totalSkipped\n";

/**
 * Download URL content with cURL
 */
function downloadUrl(string $url, int $timeout = 30): ?string {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 5,
        CURLOPT_TIMEOUT => $timeout,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false || $httpCode !== 200) {
        return null;
    }

    return $response;
}

/**
 * Parse property page and extract data
 */
function parsePropertyPage(string $html, string $url): ?array {
    // Suppress HTML parsing warnings
    libxml_use_internal_errors(true);

    $doc = new DOMDocument();
    $doc->loadHTML($html);
    $xpath = new DOMXPath($doc);

    libxml_clear_errors();

    $data = [
        'title' => null,
        'description' => null,
        'price' => null,
        'bedrooms' => null,
        'bathrooms' => null,
        'area' => null,
        'location' => null,
        'images' => [],
        'features' => [
            'has_pool' => false,
            'has_terrace' => false,
            'has_garage' => false,
            'has_sea_view' => false,
        ],
    ];

    // Extract title (h1)
    $titleNodes = $xpath->query('//h1');
    if ($titleNodes->length > 0) {
        $data['title'] = trim($titleNodes->item(0)->textContent);
    }

    // Extract description - look for main content div
    $descriptionNodes = $xpath->query('//div[contains(@class, "description") or contains(@class, "content") or contains(@class, "text")]');
    foreach ($descriptionNodes as $node) {
        $content = trim($node->textContent);
        if (strlen($content) > 100) { // Must be substantial
            $data['description'] = getInnerHTML($node);
            break;
        }
    }

    // If no description found, try article or main content areas
    if (!$data['description']) {
        $contentNodes = $xpath->query('//article | //main | //div[@id="content"]');
        if ($contentNodes->length > 0) {
            $data['description'] = getInnerHTML($contentNodes->item(0));
        }
    }

    // Extract price (look for numbers with € or EUR)
    $priceNodes = $xpath->query('//*[contains(text(), "€") or contains(text(), "EUR")]');
    foreach ($priceNodes as $node) {
        $text = $node->textContent;
        if (preg_match('/[\d.,]+\s*€|€\s*[\d.,]+/', $text, $matches)) {
            $priceStr = preg_replace('/[^\d,.]/', '', $matches[0]);
            $priceStr = str_replace(',', '', $priceStr); // Remove thousands separators
            $data['price'] = (float)$priceStr;
            break;
        }
    }

    // Extract bedrooms (look for bedroom count)
    $bedroomNodes = $xpath->query('//*[contains(text(), "bedroom") or contains(text(), "habitación") or contains(text(), "dormitorio")]');
    foreach ($bedroomNodes as $node) {
        $text = $node->textContent;
        if (preg_match('/(\d+)\s*(bedroom|habitación|dormitorio)/i', $text, $matches)) {
            $data['bedrooms'] = (int)$matches[1];
            break;
        }
    }

    // Extract bathrooms
    $bathroomNodes = $xpath->query('//*[contains(text(), "bathroom") or contains(text(), "baño")]');
    foreach ($bathroomNodes as $node) {
        $text = $node->textContent;
        if (preg_match('/(\d+)\s*(bathroom|baño)/i', $text, $matches)) {
            $data['bathrooms'] = (int)$matches[1];
            break;
        }
    }

    // Extract area (m²)
    $areaNodes = $xpath->query('//*[contains(text(), "m²") or contains(text(), "m2") or contains(text(), "sqm")]');
    foreach ($areaNodes as $node) {
        $text = $node->textContent;
        if (preg_match('/(\d+(?:[.,]\d+)?)\s*m[²2]|sqm/i', $text, $matches)) {
            $areaStr = str_replace(',', '.', $matches[1]);
            $data['area'] = (int)$areaStr;
            break;
        }
    }

    // Detect location from URL
    $urlLower = strtolower($url);
    foreach (CITY_MAPPING as $keyword => $locationData) {
        if (strpos($urlLower, $keyword) !== false) {
            $data['location'] = $locationData;
            break;
        }
    }

    // Extract images from gallery
    $imageNodes = $xpath->query('//img[contains(@src, "properties") or contains(@src, "gallery") or contains(@src, "image")]');
    foreach ($imageNodes as $node) {
        $src = $node->getAttribute('src');

        // Skip logos, icons, and small images
        if (strpos($src, 'logo') !== false ||
            strpos($src, 'icon') !== false ||
            strpos($src, 'avatar') !== false) {
            continue;
        }

        // Convert relative to absolute URL
        if (strpos($src, 'http') !== 0) {
            $parsed = parse_url($url);
            $baseUrl = $parsed['scheme'] . '://' . $parsed['host'];
            $src = $baseUrl . (strpos($src, '/') === 0 ? '' : '/') . $src;
        }

        // Check if already in array
        if (!in_array($src, $data['images'])) {
            $data['images'][] = $src;
        }
    }

    // Detect features from text content
    $bodyText = strtolower($doc->textContent);
    if (strpos($bodyText, 'pool') !== false || strpos($bodyText, 'piscina') !== false) {
        $data['features']['has_pool'] = true;
    }
    if (strpos($bodyText, 'terrace') !== false || strpos($bodyText, 'terraza') !== false) {
        $data['features']['has_terrace'] = true;
    }
    if (strpos($bodyText, 'garage') !== false || strpos($bodyText, 'garaje') !== false ||
        strpos($bodyText, 'parking') !== false) {
        $data['features']['has_garage'] = true;
    }
    if (strpos($bodyText, 'sea view') !== false || strpos($bodyText, 'vista al mar') !== false ||
        strpos($bodyText, 'vistas al mar') !== false) {
        $data['features']['has_sea_view'] = true;
    }

    // Set defaults if not found
    if (!$data['title']) {
        // Extract from URL as fallback
        $pathParts = explode('_', basename($url));
        $data['title'] = ucwords(str_replace('-', ' ', end($pathParts)));
    }

    if (!$data['description']) {
        $data['description'] = '<p>New build property in ' . ($data['location']['city'] ?? 'Spain') . '</p>';
    }

    if (!$data['bedrooms']) $data['bedrooms'] = 2; // Default
    if (!$data['bathrooms']) $data['bathrooms'] = 2; // Default
    if (!$data['area']) $data['area'] = 100; // Default
    if (!$data['price']) $data['price'] = 200000; // Default

    // Validation
    if (!$data['title'] || !$data['location']) {
        return null;
    }

    return $data;
}

/**
 * Get inner HTML of a DOMNode
 */
function getInnerHTML(DOMNode $node): string {
    $innerHTML = '';
    $children = $node->childNodes;

    foreach ($children as $child) {
        $innerHTML .= $node->ownerDocument->saveHTML($child);
    }

    // Clean up the HTML
    $innerHTML = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $innerHTML);
    $innerHTML = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $innerHTML);

    return trim($innerHTML);
}

/**
 * Get or create location in database
 */
function getOrCreateLocation(PDO $pdo, array $locationData): ?int {
    try {
        // Try to find existing location
        $stmt = $pdo->prepare("SELECT id FROM locations WHERE city = ? AND province = ?");
        $stmt->execute([$locationData['city'], $locationData['province']]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            return (int)$existing['id'];
        }

        // Create new location
        $stmt = $pdo->prepare("
            INSERT INTO locations (city, region, province)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([
            $locationData['city'],
            $locationData['region'],
            $locationData['province']
        ]);

        return (int)$pdo->lastInsertId();
    } catch (PDOException $e) {
        error_log("Location error: " . $e->getMessage());
        return null;
    }
}

/**
 * Download images and save to disk
 */
function downloadImages(array $imageUrls, string $slug): array {
    $downloaded = [];
    $maxImages = 20; // Limit to avoid excessive downloads

    foreach (array_slice($imageUrls, 0, $maxImages) as $index => $imageUrl) {
        try {
            $imageData = downloadUrl($imageUrl, 60);
            if (!$imageData) {
                echo "  ⚠ Failed to download image $index: $imageUrl\n";
                continue;
            }

            // Validate it's an image
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->buffer($imageData);

            if (!in_array($mimeType, ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'])) {
                echo "  ⚠ Skipped non-image file (type: $mimeType)\n";
                continue;
            }

            // Determine extension
            $ext = match($mimeType) {
                'image/jpeg', 'image/jpg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
                default => 'jpg'
            };

            // Generate filename: newbuild-{slug}-{index}.{ext}
            $filename = sprintf('newbuild-%s-%02d.%s', $slug, $index + 1, $ext);
            $filepath = UPLOAD_PATH . '/' . $filename;

            // Save to disk
            if (file_put_contents($filepath, $imageData) === false) {
                echo "  ⚠ Failed to save image: $filename\n";
                continue;
            }

            $downloaded[] = [
                'filename' => $filename,
                'sort_order' => $index
            ];

        } catch (Exception $e) {
            echo "  ⚠ Image download error: " . $e->getMessage() . "\n";
        }
    }

    return $downloaded;
}

/**
 * Insert property into database
 */
function insertProperty(PDO $pdo, array $data, int $typeId, int $locationId, string $slug): ?int {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO properties (
                title,
                slug,
                description,
                property_type_id,
                location_id,
                price,
                currency,
                area_sqm,
                bedrooms,
                bathrooms,
                has_pool,
                has_terrace,
                has_garage,
                has_sea_view,
                is_featured,
                is_active,
                status,
                created_at
            ) VALUES (
                ?, ?, ?, ?, ?, ?, 'EUR', ?, ?, ?,
                ?, ?, ?, ?, 1, 1, 'available', NOW()
            )
        ");

        $stmt->execute([
            $data['title'],
            $slug,
            $data['description'],
            $typeId,
            $locationId,
            $data['price'],
            $data['area'],
            $data['bedrooms'],
            $data['bathrooms'],
            $data['features']['has_pool'] ? 1 : 0,
            $data['features']['has_terrace'] ? 1 : 0,
            $data['features']['has_garage'] ? 1 : 0,
            $data['features']['has_sea_view'] ? 1 : 0,
        ]);

        return (int)$pdo->lastInsertId();
    } catch (PDOException $e) {
        error_log("Insert property error: " . $e->getMessage());
        return null;
    }
}

/**
 * Insert images into database
 */
function insertImages(PDO $pdo, int $propertyId, array $images): int {
    $count = 0;

    try {
        $stmt = $pdo->prepare("
            INSERT INTO property_images (property_id, filename, is_primary, sort_order)
            VALUES (?, ?, ?, ?)
        ");

        foreach ($images as $image) {
            $isPrimary = ($image['sort_order'] === 0) ? 1 : 0;
            $stmt->execute([
                $propertyId,
                $image['filename'],
                $isPrimary,
                $image['sort_order']
            ]);
            $count++;
        }
    } catch (PDOException $e) {
        error_log("Insert images error: " . $e->getMessage());
    }

    return $count;
}

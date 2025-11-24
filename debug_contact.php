<?php
/**
 * Debug script for contact form issue
 * Access this at: /debug_contact.php
 */
require_once __DIR__ . '/config/config.php';

echo "<h1>Contact Form Debug</h1>";
echo "<pre>";

// 1. Check database connection
echo "1. Adatbázis kapcsolat ellenőrzése...\n";
try {
    $pdo = getPDO();
    echo "   ✓ Sikeres kapcsolat\n\n";
} catch (PDOException $e) {
    echo "   ✗ HIBA: " . $e->getMessage() . "\n\n";
    exit;
}

// 2. Check if contact_inquiries table exists
echo "2. contact_inquiries tábla létezésének ellenőrzése...\n";
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'contact_inquiries'");
    $tableExists = $stmt->rowCount() > 0;

    if ($tableExists) {
        echo "   ✓ A tábla létezik\n\n";
    } else {
        echo "   ✗ HIBA: A contact_inquiries tábla NEM létezik!\n";
        echo "   → Importálnia kell a db/besthomesespana.sql fájlt phpMyAdmin-ban\n\n";

        // List all tables
        echo "3. Létező táblák az adatbázisban:\n";
        $stmt = $pdo->query("SHOW TABLES");
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            echo "   - " . $row[0] . "\n";
        }
        exit;
    }
} catch (PDOException $e) {
    echo "   ✗ HIBA: " . $e->getMessage() . "\n\n";
    exit;
}

// 3. Check table structure
echo "3. Tábla struktúra ellenőrzése...\n";
try {
    $stmt = $pdo->query("DESCRIBE contact_inquiries");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "   Oszlopok:\n";
    foreach ($columns as $col) {
        echo "   - {$col['Field']} ({$col['Type']}) " .
             ($col['Null'] === 'NO' ? 'NOT NULL' : 'NULL') .
             ($col['Default'] !== null ? " DEFAULT '{$col['Default']}'" : '') . "\n";
    }
    echo "\n";
} catch (PDOException $e) {
    echo "   ✗ HIBA: " . $e->getMessage() . "\n\n";
    exit;
}

// 4. Test insert
echo "4. Teszt beszúrás próba...\n";
try {
    $testName = 'Debug Test ' . date('Y-m-d H:i:s');
    $testEmail = 'debug@test.com';
    $testPhone = '123456789';
    $testMessage = 'Ez egy debug teszt üzenet';
    $testSource = 'debug';

    $stmt = $pdo->prepare("
        INSERT INTO contact_inquiries (name, email, phone, message, source_page)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$testName, $testEmail, $testPhone, $testMessage, $testSource]);

    $insertId = $pdo->lastInsertId();
    echo "   ✓ Sikeres beszúrás! ID: $insertId\n";
    echo "   → A contact form most működnie kellene!\n\n";

    // Clean up test data
    $stmt = $pdo->prepare("DELETE FROM contact_inquiries WHERE id = ?");
    $stmt->execute([$insertId]);
    echo "   ✓ Teszt adat törölve\n\n";

} catch (PDOException $e) {
    echo "   ✗ BESZÚRÁSI HIBA: " . $e->getMessage() . "\n";
    echo "   SQL State: " . $e->getCode() . "\n\n";

    echo "MEGOLDÁS:\n";
    echo "=========\n";
    if (strpos($e->getMessage(), "doesn't exist") !== false) {
        echo "A contact_inquiries tábla nem létezik.\n";
        echo "→ Importálja a db/besthomesespana.sql fájlt phpMyAdmin-ban!\n";
    } elseif (strpos($e->getMessage(), "Unknown column") !== false) {
        echo "Valamelyik oszlop nem létezik a táblában.\n";
        echo "→ Frissítse a tábla struktúrát az alábbi SQL-lel:\n\n";
        echo "DROP TABLE IF EXISTS contact_inquiries;\n\n";
        echo file_get_contents(__DIR__ . '/db/contact_inquiries.sql.txt') ?:
             "Másolja be a CREATE TABLE részét a db/besthomesespana.sql fájlból.\n";
    } else {
        echo "Egyéb adatbázis hiba.\n";
        echo "→ Ellenőrizze a PHP error log-ot további részletekért.\n";
    }
    exit;
}

echo "5. MINDEN MŰKÖDIK! ✓\n";
echo "   A contact form használatra kész.\n";
echo "   Ha még mindig hiba van, törölje a böngésző cache-t.\n\n";

echo "BEFEJEZÉS\n";
echo "=========\n";
echo "Ezt a debug fájlt most már törölheti: /debug_contact.php\n";

echo "</pre>";

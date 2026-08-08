<?php
// database/update_views.php
// Skrypt CLI: Automatyczna aktualizacja widoków SQL z katalogu database/views/

// 1. Ładowanie autoloadera z Composera
require_once __DIR__ . '/../vendor/autoload.php';

// Wyciągamy globalną instancję PDO z pamięci aplikacji
global $db;

if (!$db) {
    echo "❌ Błąd: Brak połączenia z bazą danych (\$db nie jest zainicjalizowane).\n";
    exit(1);
}

// 2. Pobieramy wszystkie pliki .sql z katalogu database/views/
$viewsDir = __DIR__ . '/views';
$files = glob($viewsDir . '/*.sql');

if (empty($files)) {
    echo "ℹ️ Brak plików .sql w katalogu {$viewsDir}\n";
    exit(0);
}

// 3. Sortujemy po nazwach (prefiksy 01_, 02_ ukierunkują poprawną kolejność kaskady)
sort($files);

echo "🔄 Aktualizacja widoków SQL...\n";
echo "--------------------------------------------------\n";

$successCount = 0;

foreach ($files as $filePath) {
    $fileName = basename($filePath);
    $sql = file_get_contents($filePath);

    if (empty(trim($sql))) {
        echo "⚠️  [POMINIĘTO] {$fileName} (pusty plik)\n";
        continue;
    }

    try {
        $db->exec($sql);
        echo "✅ [OK] {$fileName}\n";
        $successCount++;
    } catch (\PDOException $e) {
        echo "❌ [BŁĄD] {$fileName}\n";
        echo "   Komunikat: " . $e->getMessage() . "\n";
        echo "--------------------------------------------------\n";
        echo "⛔ Wykonywanie przerwane ze względu na błąd w strukturze SQL.\n";
        exit(1);
    }
}

echo "--------------------------------------------------\n";
echo "🚀 Gotowe! Pomyślnie wdrożono {$successCount} widoków.\n";
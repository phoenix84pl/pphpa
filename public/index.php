<?php
// 1. Ładujemy autoloader (on sam zaorze i wciągnie silnik z folderu vendor)
require_once __DIR__ . '/../vendor/autoload.php';

// 2. Testujemy klasę z Twojego rdzenia pphpc
$coreTest = new \PPHPC\CoreTest();

// 3. Przygotowanie danych aplikacji
$tytulSekcji = "Projekty w systemie PPHPS";
$projekty = [
    ['id' => 1, 'nazwa' => 'Silnik PPHPC', 'status' => 'Aktywny'],
    ['id' => 2, 'nazwa' => 'Szablon PPHPS', 'status' => 'Wdrożony']
];

// 4. Załadowanie widoku HTML
require_once __DIR__ . '/../resources/views/index.phtml';
?>
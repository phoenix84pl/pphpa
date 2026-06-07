<?php
// pphpa/public/index.php

// 1. Ładowanie automatycznego ładowacza klas (Autoloader z Composera)
require_once __DIR__ . '/../vendor/autoload.php';

// 2. ŁADOWANIE ZMIENNYCH ŚRODOWISKOWYCH (.env)
try {
    // Szuka pliku .env w głównym katalogu projektu (poziom wyżej niż public/)
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->load();
} catch (\Exception $e) {
    // Jeśli pliku .env nie ma (np. świeża instalacja), aplikacja idzie dalej,
    // ale baza danych nie zostanie skonfigurowana.
}

// 3. Tworzenie fabryki żądań PSR-7 na podstawie zmiennych globalnych serwera
$psr17Factory = new \Nyholm\Psr7\Factory\Psr17Factory();
$creator = new \Nyholm\Psr7Server\ServerRequestCreator(
    $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory
);
$request = $creator->fromGlobals();


// 4. OBSŁUGA BAZY DANYCH ($db)
$db = null;

try {
    if (class_exists('\Phoenix\Core\Database')) {
        // Jeśli w .env zdefiniowano dane logowania, przekazujemy je do konstruktora
        if (isset($_ENV['DB_HOST'])) {
            $db = new \Phoenix\Core\Database(
                $_ENV['DB_HOST'],
                $_ENV['DB_USER'],
                $_ENV['DB_PASS'],
                $_ENV['DB_NAME']
            );
        } else {
            // Bezpiecznik: jeśli brak .env, tworzymy pusty obiekt, 
            // dzięki czemu system i trasa /ping nie umrą.
            $db = new \Phoenix\Core\Database();
        }
    }
} catch (\Throwable $e) {
    // Jeśli cokolwiek wywali się przy bazie, wyłapujemy to.
    // Zapisujemy błąd do logów, ale pozwalamy aplikacji działać dalej!
    error_log("Database initialization failed: " . $e->getMessage());
}

// 5. Inicjalizacja profesjonalnego Routera (wskazujemy folder na widoki .phtml)
$router = new \Phoenix\Core\Router(__DIR__ . '/../resources/views');


// ----------------------------------------------------------------------
// SYSTEMOWE TRASY SZABLONU (ROUTING PROGRAMISTYCZNY)
// ----------------------------------------------------------------------

// Trasa "/" odpali automatycznie resources/views/index.phtml (dzięki fallbackowi w Core)

$router->get('/api/status', function($request) {
    return new \Nyholm\Psr7\Response(
        200, 
        ['Content-Type' => 'application/json'], 
        json_encode(['status' => 'running', 'framework' => 'Phoenix Core'])
    );
});


// ----------------------------------------------------------------------
// OBSŁUGA ŻĄDANIA I EMISJA ODPOWIEDZI
// ----------------------------------------------------------------------

// 6. Przetwarzanie aktualnego adresu URL przez silnik Routera
$response = $router->handle($request);

// 7. Emisja kodu statusu i nagłówków HTTP do przeglądarki
http_response_code($response->getStatusCode());
foreach ($response->getHeaders() as $name => $values) {
    foreach ($values as $value) {
        header(sprintf('%s: %s', $name, $value), false);
    }
}

// 8. Wyplucie właściwej treści strony (w tym naszych widoków .phtml)
echo $response->getBody();
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
        if (isset($_ENV['DB_HOST']) && $_ENV['DB_HOST'] !== '') {
            $db = \Phoenix\Core\Database::getInstance([
                'host' => $_ENV['DB_HOST'],
                'user' => $_ENV['DB_USER'],
                'pass' => $_ENV['DB_PASS'],
                'name' => $_ENV['DB_NAME']
            ]);
        } else {
            $db = \Phoenix\Core\Database::getInstance();
        }
    }
} catch (\Throwable $e) {
    // ARCHITEKTURA: Nie zabijamy systemu przez die(). 
    // Przekazujemy złapany błąd ($e) dalej, żeby kontroler mógł go obsłużyć.
    $db = $e;
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
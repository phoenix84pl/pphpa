<?php
// pphpa/public/index.php

// 1. Ładowanie automatycznego ładowacza klas (Autoloader z Composera)
require_once __DIR__ . '/../vendor/autoload.php';

// 2. Tworzenie fabryki żądań PSR-7 na podstawie zmiennych globalnych serwera
$psr17Factory = new \Nyholm\Psr7\Factory\Psr17Factory();
$creator = new \Nyholm\Psr7Server\ServerRequestCreator(
    $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory
);
$request = $creator->fromGlobals();


// 3. OBSŁUGA BAZY DANYCH ($db)
// Inicjalizujemy zmienną. Jeśli w silniku (Core) istnieje klasa Database, tworzymy jej obiekt.
$db = null;
if (class_exists('\Phoenix\Core\Database')) {
    // W przyszłości parametry połączenia (host, user, pass) wyciągniemy stąd do pliku konfiguracyjnego
    $db = new \Phoenix\Core\Database();
}


// 4. Inicjalizacja profesjonalnego Routera (wskazujemy folder na widoki .phtml)
$router = new \Phoenix\Core\Router(__DIR__ . '/../resources/views');


// ----------------------------------------------------------------------
// SYSTEMOWE TRASY SZABLONU (ROUTING PROGRAMISTYCZNY)
// ----------------------------------------------------------------------

// Trasa "/" odpali automatycznie resources/views/index.phtml (dzięki fallbackowi w Core)

$router->get('/ping', function($request) {
    return "pong";
});

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

// 5. Przetwarzanie aktualnego adresu URL przez silnik Routera
$response = $router->handle($request);

// 6. Emisja kodu statusu i nagłówków HTTP do przeglądarki
http_response_code($response->getStatusCode());
foreach ($response->getHeaders() as $name => $values) {
    foreach ($values as $value) {
        header(sprintf('%s: %s', $name, $value), false);
    }
}

// 7. Wyplucie właściwej treści strony (w tym naszych widoków .phtml)
echo $response->getBody();
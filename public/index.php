<?php
// public/index.php

// 1. Autoloader Composera
require_once __DIR__ . '/../vendor/autoload.php';

// 2. Rozruch środowiska (wczytanie .env oraz bazy $db z PPHPC)
\Phoenix\Core\Bootstrap::init(dirname(__DIR__));

// 3. Tworzenie fabryki PSR-7
$psr17Factory = new \Nyholm\Psr7\Factory\Psr17Factory();
$creator = new \Nyholm\Psr7Server\ServerRequestCreator(
    $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory
);
$request = $creator->fromGlobals();

// 4. Inicjalizacja Routera
$router = new \Phoenix\Core\Router(__DIR__ . '/../views');

// 5. Trasa statusowa API
$router->get('/api/status', function($request) {
    return new \Nyholm\Psr7\Response(
        200, 
        ['Content-Type' => 'application/json'], 
        json_encode(['status' => 'running', 'framework' => 'Phoenix Core'])
    );
});

// 6. Obsługa żądania i emisja odpowiedzi
$response = $router->handle($request);

http_response_code($response->getStatusCode());
foreach ($response->getHeaders() as $name => $values) {
    foreach ($values as $value) {
        header(sprintf('%s: %s', $name, $value), false);
    }
}

echo $response->getBody();
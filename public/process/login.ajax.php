<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../vendor/autoload.php';

$response = [
    'status' => 'success',
    'message' => 'Skrypt procesowy działa prawidłowo.',
    'timestamp' => time()
];

echo json_encode($response);
exit;
?>
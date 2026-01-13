<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Infrastructure\Web\ApiRequestHandler;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Authorization, Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$uri = $_SERVER['REQUEST_URI'] ?? '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Обработка FormData и JSON данных
if ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($_SERVER['CONTENT_TYPE'] ?? '', 'multipart/form-data') === 0) {
    $data = $_POST;
    if (isset($data['data'])) {
        $data = json_decode($data['data'], true) ?? [];
    }
} else {
    $data = json_decode(file_get_contents('php://input'), true) ?? [];
}

try {
    $handler = new ApiRequestHandler();
    $response = $handler->handle($uri, $method, $data);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_SLASHES);
    exit;
}

echo json_encode($response, JSON_UNESCAPED_SLASHES);

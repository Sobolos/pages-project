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
$data = json_decode(file_get_contents('php://input'), true) ?? [];

$handler = new ApiRequestHandler();
$response = $handler->handle($uri, $method, $data);

http_response_code(isset($response['error']) ? 404 : 200);
echo json_encode($response, JSON_UNESCAPED_SLASHES);
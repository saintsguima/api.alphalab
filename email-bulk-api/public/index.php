<?php
declare (strict_types = 1);

ini_set('display_errors', ($_ENV['APP_ENV'] ?? 'prod') !== 'prod' ? '1' : '0');
error_reporting(E_ALL);

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Config\Env;
use App\Router;

try {
    Env::load();
} catch (Throwable $e) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Erro ao carregar .env: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
    exit;
}

try {
    $router = new Router();
    $router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
} catch (Throwable $e) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'internal_error', 'message' => $e->getMessage()]);
}

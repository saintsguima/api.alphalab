<?php

declare (strict_types = 1);

ini_set('display_errors', '1');
error_reporting(E_ALL);

use App\Config\Env;
use App\Router;

require dirname(__DIR__) . '/vendor/autoload.php';

Env::load();

try {
    $router = new Router();
    $router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
} catch (Throwable $e) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo "ERRO: " . $e->getMessage() . "\n\n" . $e->getTraceAsString();
}

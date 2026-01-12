<?php
namespace App;

use App\Controllers\EmailJobController;

final class Router
{
    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?? '/';

        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $basePath   = rtrim(dirname($scriptName), '/');
        if ($basePath && str_starts_with($path, $basePath)) {
            $path = substr($path, strlen($basePath)) ?: '/';
        }

        if ($method === 'GET' && $path === '/') {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'ok', 'time' => date('c')]);
            return;
        }

        if ($method === 'POST' && $path === '/jobs/send-bulk-email') {
            (new EmailJobController())->sendBulk();
            return;
        }

        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'route_not_found', 'path' => $path]);
    }
}

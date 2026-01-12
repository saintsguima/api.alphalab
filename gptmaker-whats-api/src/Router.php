<?php
namespace App;

use App\Controllers\JobController;
use App\Controllers\WebhookController;

final class Router
{
    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?? '/';

        // Descobre o base path (ex.: /gptmaker-whats-api/public)
        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $basePath   = rtrim(dirname($scriptName), '/'); // /gptmaker-whats-api/public

        // Remove o base path do começo do $path
        if ($basePath && str_starts_with($path, $basePath)) {
            $path = substr($path, strlen($basePath));
            if ($path === '' || $path === false) {
                $path = '/';
            }
        }

        // Roteamento
        if ($method === 'GET' && $path === '/') {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'ok', 'time' => date('c')]);
            return;
        }

        if ($method === 'POST' && $path === '/jobs/notify-defaulters') {
            (new JobController())->notifyDefaulters();
            return;
        }

        if ($method === 'POST' && $path === '/webhooks/zapi') {
            (new WebhookController())->receiveZapi();
            return;
        }

        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'route_not_found', 'path' => $path]);
    }
}

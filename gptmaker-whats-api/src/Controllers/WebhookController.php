<?php
namespace App\Controllers;

use App\Services\Signature;

final class WebhookController
{
    public function receiveZapi(): void
    {
        $raw = file_get_contents('php://input') ?: '';
        $sig = $_SERVER['HTTP_X_SIGNATURE'] ?? null;

        if (! Signature::validate($sig, $raw)) {
            http_response_code(401);
            echo json_encode(['error' => 'invalid_signature']);
            return;
        }

        $payload = json_decode($raw, true);
        // Aqui você pode gravar em uma tabela de logs, se quiser:
        // Ex.: tipo=received, from=$payload['from'], body=$payload['text'], thread_id...
        // Como o GPTMaker mantém o contexto, esse webhook é opcional.

        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'ok']);
    }
}

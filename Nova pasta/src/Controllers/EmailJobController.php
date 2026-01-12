<?php
namespace App\Controllers;

use App\Config\Env;
use App\Database\Connection;
use App\Repositories\ClienteRepository;
use App\Services\EmailService;

final class EmailJobController
{
    public function sendBulk(): void
    {
        header('Content-Type: application/json');

        $pdo   = Connection::make();
        $repo  = new ClienteRepository($pdo);
        $email = new EmailService();

        $somenteAtivos = (int) Env::get('ONLY_ACTIVE', '1');
        $limite        = (int) Env::get('LIMIT_SEND', '0');

        $subject  = Env::get('MAIL_SUBJECT', 'Aviso');
        $bodyTxt  = (string) Env::get('MAIL_BODY_TXT', 'Olá {nome},\nMensagem.');
        $bodyHtml = (string) Env::get('MAIL_BODY_HTML', '<p>Olá <strong>{nome}</strong>,</p><p>Mensagem.</p>');

        $destinatarios = $repo->listarClientes();

        $ok    = 0;
        $falha = 0;
        $logs  = [];
        foreach ($destinatarios as $c) {
            $nome = $c['Nome'] ?? '';
            //$emailDest = $c['Email'] ?? '';
            $emailDest = 'wladdg@gmail.com';

            // Personalização simples
            $txt  = strtr($bodyTxt, ['{nome}' => $nome]);
            $html = strtr($bodyHtml, ['{nome}' => $nome]);

            try {
                $email->enviar($emailDest, $nome, $subject, $html, $txt);
                $ok++;
                $logs[] = ['email' => $emailDest, 'status' => 'enviado'];
            } catch (\Throwable $e) {
                $falha++;
                $logs[] = ['email' => $emailDest, 'status' => 'erro', 'msg' => $e->getMessage()];
            }
        }

        echo json_encode([
            'status'   => 'ok',
            'enviados' => $ok,
            'falhas'   => $falha,
            'detalhes' => $logs,
        ]);
    }
}

<?php
namespace App\Controllers;

use App\Database\Connection;
use App\Repositories\ConversaRepository;
use App\Repositories\PagamentoRepository;
use App\Services\GPTMakerClient;
use App\Services\MensageriaService;
use DateTime;

final class JobController
{
    public function notifyDefaulters(): void
    {
        header('Content-Type: application/json');

        $pdo      = Connection::make();
        $repo     = new PagamentoRepository($pdo);
        $vencidos = $repo->listarPendentes(new DateTime()); // DtPagamento < agora AND Fl_Conciliado=0

        if (! $vencidos) {
            echo json_encode(['status' => 'ok', 'message' => 'Nenhum pendente.']);
            return;
        }

        $gpt  = new GPTMakerClient();
        $conv = new ConversaRepository($pdo);
        $msg  = new MensageriaService($gpt, $conv);

        // Template da mensagem
        $template = "Olá {nome}, detectamos que o seu pagamento está pendente. "
            . "Caso já tenha pago, enviar o comprovante de pagamento."
            . "Se ainda não pagou, entre em contato, pelo telefone (21) 99999-9999. Obrigado!";

        $ok       = 0;
        $fail     = 0;
        $detalhes = [];
        foreach ($vencidos as $row) {
            $nome = $row['Nome'];
            //$fone = $row['Telefone'];
            $fone = '11993392894';

            try {
                // Você pode gerar um link único p/ confirmação
                $vars = [
                    'nome' => $nome,
                    'link' => 'https://seu-dominio.com/confirmar?tel=' . urlencode($fone), // ajuste
                ];
                $threadId = $msg->enviarComContexto($fone, $nome, $template, $vars);
                $ok++;
                $detalhes[] = ['telefone' => $fone, 'status' => 'enviado', 'thread_id' => $threadId];
            } catch (\Throwable $e) {
                $fail++;
                $detalhes[] = ['telefone' => $fone, 'status' => 'erro', 'erro' => $e->getMessage()];
            }
        }

        echo json_encode([
            'status'   => 'ok',
            'enviados' => $ok,
            'falhas'   => $fail,
            'detalhes' => $detalhes,
        ]);
    }
}

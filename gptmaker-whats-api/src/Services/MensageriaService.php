<?php
namespace App\Services;

use App\Repositories\ConversaRepository;
use App\Utils\StrTpl;

final class MensageriaService
{
    public function __construct(
        private GPTMakerClient $gpt,
        private ConversaRepository $conversas
    ) {}

    /**
     * Dispara mensagem para um contato, preservando/reutilizando contexto.
     * $template: string com placeholders {nome}, {valor}, {link}, etc.
     * $vars: ['nome'=>'...','valor'=>'...','link'=>'...']
     * Retorna threadId resolvido.
     */
    public function enviarComContexto(string $telefone, string $nome, string $template, array $vars): ?string
    {
        $template_final = str_replace('{nome}', $nome, $template);
        $texto          = StrTpl::render($template_final, $vars);
        $agentId        = $this->gptAgentId(); // helper
        $existente      = $this->conversas->obterPorTelefone($agentId, $telefone);
        $threadId       = $existente['ThreadId'] ?? null;

        $resp = $this->gpt->enviarMensagemWhatsapp($telefone, $texto, $threadId);

        // Ajuste a chave conforme o retorno real do GPTMaker
        $novoThreadId = $resp['thread_id'] ?? $threadId ?? null;

        // Atualiza/insere conversa
        $this->conversas->upsert($agentId, $telefone, $novoThreadId);
        return $novoThreadId;
    }

    private function gptAgentId(): string
    {
        return (string) ($_ENV['GPTMAKER_AGENT_ID'] ?? '');
    }
}

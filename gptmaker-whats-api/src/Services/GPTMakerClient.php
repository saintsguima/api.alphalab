<?php
namespace App\Services;

use App\Config\Env;
use GuzzleHttp\Client;
use RuntimeException;

final class GPTMakerClient
{
    private Client $http;
    private string $baseUrl;
    private string $apiKey;
    private string $agentId;
    private string $channelId;
    private string $restPath;
    private string $defaultCountry;

    public function __construct()
    {
        $this->baseUrl        = rtrim(Env::get('GPTMAKER_BASE_URL', ''), '/');
        $this->apiKey         = (string) Env::get('GPTMAKER_API_KEY', '');
        $this->agentId        = (string) Env::get('GPTMAKER_AGENT_ID', '');
        $this->channelId      = (string) Env::get('GPTMAKER_CHANNEL_ID', '');
        $this->restPath       = (string) Env::get('GPTMAKER_REST_PATHS', '');
        $this->defaultCountry = (string) Env::get('DEFAULT_COUNTRY_CODE', '55');

        if (! $this->baseUrl || ! $this->apiKey || ! $this->agentId) {
            throw new RuntimeException('GPTMaker configs ausentes em .env');
        }

        $this->http = new Client([
            'base_uri'     => $this->baseUrl . '/',
            'timeout'      => 20,
            'headers'      => [
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    /**
     * Envia uma mensagem de WhatsApp via agente GPTMaker.
     * Retorna array com possivel 'thread_id' resolvido.
     *
     * @param string $telefone E.164 sem '+': ex. 55999887766
     * @param string $texto
     * @param string|null $threadId se já tiver; reusa contexto
     */
    public function enviarMensagemWhatsapp(string $telefone, string $texto, ?string $threadId = null): array
    {
        // Normaliza telefone (adicione validações a gosto)
        $fone = preg_replace('/\D+/', '', $telefone);
        if (! str_starts_with($fone, $this->defaultCountry)) {
            $fone = $this->defaultCountry . $fone; // 55 + número
        }

        // EXEMPLO DE ENDPOINT/PAYLOAD — AJUSTE PARA O REAL DO GPTMAKER
        $payload = [
            'phone'   => $fone, // amarra por contato
            'message' => $texto,
        ];

        $restPathNew = str_replace('{GPTMAKER_CHANNEL_ID}', $this->channelId, $this->restPath);
        //$res = $this->http->post('/v2/channel/3E8C2EF4A97070908E96528CCCFB2316/start-conversation', [
        $res = $this->http->post($restPathNew, [
            'json' => $payload,
        ]);

        $status = $res->getStatusCode();
        $body   = json_decode((string) $res->getBody(), true);
        if ($status >= 300) {
            throw new RuntimeException('Falha GPTMaker: ' . $status . ' ' . json_encode($body));
        }
        return $body ?: [];
    }
}

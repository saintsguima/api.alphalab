<?php
header("Access-Control-Allow-Origin: *"); // ou coloque o domínio exato, ex: http://localhost
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

// RESPOSTA AO PRE-FLIGHT CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Opcional: defina tempo de cache do preflight
    header('Access-Control-Max-Age: 86400');
    http_response_code(200);
    exit();
}

require '../DbConnection/conexao.php';
require '../env/env.php';

$data = json_decode(file_get_contents('php://input'), true);

if ($this->TemDisparo($pdo)) {
    $this->set2No($pdo);
    $this->chamarApiNotificacao($urlEmail);
} else {
    $this->justAdjustNextTime($pdo);
}

function justAdjustNextTime($pdo)
{
    $sql  = "UPDATE Send SET NextEmail = NOW() + INTERVAL 30 MINUTE";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

}
function set2No($pdo)
{
    $sql  = "UPDATE Send SET flEmail = 0, LastEmail = NOW(), NextEmail = NOW() + INTERVAL 30 MINUTE";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
}

function TemDisparo($pdo)
{
    $sql  = "SELECT flEmail from Send Limit 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($resultado) {
        $flEmail_variavel = $resultado['flEmail'];

        return (bool) $flEmal_variavel;
    }

    return false;

}

function chamarApiNotificacao($urlEmail)
{
    $url = $urlEmail;
    $ch  = curl_init($url);

                                                    // Configurações básicas para uma requisição POST simples
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Não exibe a resposta diretamente
    curl_setopt($ch, CURLOPT_POST, true);           // Define o método como POST
    curl_setopt($ch, CURLOPT_POSTFIELDS, "{}");     // Envia um corpo de requisição vazio (ou dados necessários pela API)
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

    // Executa a requisição
    $response  = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // Verifica se houve erro
    if (curl_errno($ch)) {
        error_log('Erro ao chamar API de Email: ' . curl_error($ch));
        curl_close($ch);
        return false; // Falha na chamada
    }

    curl_close($ch);

    // Verifica o código de status HTTP (200, 201, etc. indicam sucesso)
    if ($http_code >= 200 && $http_code < 300) {
        // Sucesso
        error_log('API de Email chamada com sucesso. Status: ' . $http_code);
        return true;
    } else {
        // Erro na API (status 4xx ou 5xx)
        error_log('Falha na chamada da API de Email. Status: ' . $http_code . ' Resposta: ' . $response);
        return false;
    }
}

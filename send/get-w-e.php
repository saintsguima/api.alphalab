<?php
// Define os cabeçalhos para permitir CORS e indicar que a resposta é JSON
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

// RESPOSTA AO PRE-FLIGHT CORS (Mantenha este bloco)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Max-Age: 86400');
    http_response_code(200);
    exit();
}

// Garante que a conexão com o banco de dados está incluída
require '../DbConnection/conexao.php';

// O $data não é usado nesta rotina, mas pode ser útil se ela receber dados
$data = json_decode(file_get_contents('php://input'), true);

try {
    $sql  = "SELECT LastWhatsapp, NextWhatsapp, LastEmail, NextEmail FROM Send LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    // Busca a linha como um array associativo
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($resultado) {
        // Retorna os campos encontrados em formato JSON
        // O PHP já converte o array associativo em um objeto JSON válido.
        echo json_encode([
            'status' => 'success',
            'data'   => $resultado, // Contém LastWhatsapp, NextWhatsapp, etc.
        ]);

    } else {
        // Nenhuma linha retornada
        http_response_code(404);
        echo json_encode([
            'status'  => 'error',
            'message' => 'Configurações de envio não encontradas.',
        ]);
    }

} catch (PDOException $e) {
    // Captura erros de banco de dados
    http_response_code(500);
    error_log("Erro de DB: " . $e->getMessage()); // Registra o erro internamente
    echo json_encode([
        'status'  => 'error',
        'message' => 'Erro interno do servidor.',
    ]);
}

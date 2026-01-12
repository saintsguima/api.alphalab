<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

// Trata pré-via (preflight) OPTIONS:
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Opcional: pode mandar cache para a resposta OPTIONS, se quiser
    header('Access-Control-Max-Age: 86400');
    http_response_code(200); // Retorna 200 OK sempre!
    exit();
}

// --- Resto do seu código POST ---
require '../DbConnection/conexao.php';
$data = json_decode(file_get_contents('php://input'), true);

$clienteId = $data['clienteId'] ?? '';
$ativo     = $data['ativo'] ?? '1';

$sql  = "UPDATE cliente SET EnvioEmail = :ativo WHERE Id = :clienteId";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':clienteId' => $clienteId,
    ':ativo'     => $ativo,
]);

if ($stmt->rowCount() === 1) {
    echo json_encode(['status' => 'ok', 'mensagem' => 'Ação executada com sucesso.']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao executar a ação.']);
}

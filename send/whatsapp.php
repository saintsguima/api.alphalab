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

$data = json_decode(file_get_contents('php://input'), true);

$flWhatsapp = $data['flWhatsapp'] ?? '1';

$sql = "UPDATE Send SET flWhatsapp = 1";

$stmt = $pdo->prepare($sql);
$stmt->execute();

if ($stmt->rowCount() >= 0) {
    echo json_encode(['status' => 'ok', 'mensagem' => 'Disparo do Whatsapp registrado com sucesso.']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao registrar o disparo do Whatsapp.']);
}

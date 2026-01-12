<?php
header("Access-Control-Allow-Origin: *"); // ou defina o domínio específico
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

// Após OPTIONS, prossegue para lógica de SELECT
require '../DbConnection/conexao.php';
$data = json_decode(file_get_contents('php://input'), true);

$clienteId = $data['clienteId'] ?? '';

$sql = "select
    Nome,
    CPF,
    Telefone,
    Email,
    Ativo,
    EnvioWhatsapp,
    EnvioEmail
from
    cliente
WHERE
    Id = :clienteId";

$stmt = $pdo->prepare($sql);
$stmt->execute([':clienteId' => $clienteId]);

if ($stmt->rowCount() === 1) {
    $resultado     = $stmt->fetch(PDO::FETCH_ASSOC);
    $Nome          = $resultado['Nome'];
    $CPF           = $resultado['CPF'];
    $Telefone      = $resultado['Telefone'];
    $Email         = $resultado['Email'];
    $Ativo         = $resultado['Ativo'];
    $EnvioWhatsapp = $resultado['EnvioWhatsapp'];
    $EnvioEmail    = $resultado['EnvioEmail'];

    echo json_encode([
        'status'        => 'ok',
        'Nome'          => $Nome,
        'CPF'           => $CPF,
        'Email'         => $Email,
        'Telefone'      => $Telefone,
        'Id'            => $clienteId,
        'Ativo'         => $Ativo,
        'EnvioWhatsapp' => $EnvioWhatsapp,
        'EnvioEmail'    => $EnvioEmail,
    ]);
} else {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Cliente não existe em nossa base de dados.']);
}

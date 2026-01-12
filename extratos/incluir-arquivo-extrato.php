<?php
header("Access-Control-Allow-Origin: *"); // ou restrinja ao domínio da sua app
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require '../DbConnection/conexao.php'; // certifique-se de que $pdo usa ERRMODE_EXCEPTION

$input = file_get_contents('php://input');
$data  = json_decode($input, true) ?? [];

$Nome = trim($data['Nome'] ?? '');

try {
    if ($Nome === '') {
        http_response_code(400);
        echo json_encode(['status' => 'erro', 'mensagem' => 'Campo "Nome" é obrigatório.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $sqlInsert = "INSERT INTO ArquivoExtrato (Nome) VALUES (:Nome)";
    $stmtIns = $pdo->prepare($sqlInsert);
    $stmtIns->execute([':Nome' => $Nome]);

    if ($stmtIns->rowCount() === 1) {
        $Id = (int)$pdo->lastInsertId();
        http_response_code(201);
        echo json_encode(['status' => 'ok', 'Id' => $Id], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Se não inseriu por algum motivo não-exceção:
    http_response_code(500);
    echo json_encode(['status' => 'erro', 'mensagem' => 'Falha ao inserir registro.'], JSON_UNESCAPED_UNICODE);
    exit;

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'status'   => 'erro',
        'mensagem' => 'Erro na execução do registro: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

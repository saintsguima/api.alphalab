<?php
header("Access-Control-Allow-Origin: *"); // ou coloque o domínio exato, ex: http://localhost
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');
require '../DbConnection/conexao.php';
$data = json_decode(file_get_contents('php://input'), true);

$Id      = $data["Id"] ?? '';
$VlTotal = $data["VlTotal"] ?? '';

try {

    $sql = "UPDATE ContasReceber set VlTotal = :VlTotal  WHERE Id = :Id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':Id'      => $Id,
        ':VlTotal' => $VlTotal,
    ]);

    echo json_encode(['status' => 'ok', 'mensagem' => 'Registro excluido com sucesso.']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao excluir o registro.<br/>' . $e->getMessage()]);
}
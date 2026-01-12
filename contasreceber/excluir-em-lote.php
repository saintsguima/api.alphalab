<?php
header("Access-Control-Allow-Origin: *"); // ou coloque o domínio exato, ex: http://localhost
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');
require '../DbConnection/conexao.php';
$data = json_decode(file_get_contents('php://input'), true);

try {
    $wd = $data["wd"] ?? '';

    $sql = "DELETE FROM  dlCargaCliente WHERE NomeArquivo = :wd";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':wd' => $wd,
    ]);

    echo json_encode(['status' => 'ok', 'mensagem' => 'Registros excluidos com sucesso.']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao excluir os registros.<br/>' . $e->getMessage()]);
}

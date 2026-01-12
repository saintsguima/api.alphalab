<?php
header("Access-Control-Allow-Origin: *"); // ou coloque o domínio exato, ex: http://localhost
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');
require '../DbConnection/conexao.php';

// Função para limpar pontuação
function onyNumber(string $valor): string
{
    return preg_replace('/\D/', '', $valor);
}

$data = json_decode(file_get_contents('php://input'), true);

$IdCliente = $data['IdCliente'] ?? '';
$NomeCC    = $data['NomeCC'] ?? '';
$CPFCNPJ   = isset($data['theCPF']) ? onyNumber($data['theCPF']) : '';

$sql = "INSERT INTO ClienteCC (
    IdCliente,
    NomeCC,
    CPFCNPJ
) VALUES (
   :IdCliente,
   :NomeCC,
   :CPFCNPJ
)";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':IdCliente' => $IdCliente,
    ':NomeCC'    => $NomeCC,
    ':CPFCNPJ'   => $CPFCNPJ,
]);

if ($stmt->rowCount() === 1) {
    $userId = $pdo->lastInsertId();
    //echo json_encode(['status' => 'ok', 'userId' => $userId, 'nome' => $nome, 'username' => $userName, 'email' => $email]);
    echo json_encode(['status' => 'ok', 'mensagem' => 'Registro incluido com sucesso.']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao inserir registro.']);
}

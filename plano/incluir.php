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

// Função para limpar pontuação
function onyNumber(string $valor): string
{
    return preg_replace('/\D/', '', $valor);
}

$data = json_decode(file_get_contents('php://input'), true);

$nome = $data['nome'] ?? '';

// Verificar se o e-mail já está cadastrado
$checkEmailSql = "SELECT COUNT(*) FROM Plano WHERE Nome = :nome";
$stmtCheck     = $pdo->prepare($checkEmailSql);
$stmtCheck->execute([':nome' => $nome]);
$emailJaExiste = $stmtCheck->fetchColumn();

if ($emailJaExiste > 0) {
    http_response_code(403);
    echo json_encode(['status' => 'erro', 'mensagem' => 'Plano já cadastrado.']);
    exit;
}

$sql  = "INSERT INTO Plano(Nome) VALUES (";
$sql .= ":nome)";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':nome' => $nome,
]);

if ($stmt->rowCount() === 1) {
    $userId = $pdo->lastInsertId();
    //echo json_encode(['status' => 'ok', 'userId' => $userId, 'nome' => $nome, 'username' => $userName, 'email' => $email]);
    echo json_encode(['status' => 'ok', 'mensagem' => 'Registro incluido com sucesso.']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao inserir Exceção.']);
}

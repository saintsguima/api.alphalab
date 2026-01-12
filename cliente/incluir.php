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

$nome           = $data['nome'] ?? '';
$cpf            = isset($data['cpf']) ? onyNumber($data['cpf']) : '';
$telefone       = $data['telefone'] ?? '';
$email          = $data['email'] ?? '';
$ativo          = $data['ativo'] ?? '1'; // true/false para 1/0
$$enviowhatsapp = $data['enviowhatsapp'] ?? '1';
$envioemail     = $data['envioemail'] ?? '1';

// Verificar se o e-mail já está cadastrado
$checkEmailSql = "SELECT COUNT(*) FROM cliente WHERE Email = :email";
$stmtCheck     = $pdo->prepare($checkEmailSql);
$stmtCheck->execute([':email' => $email]);
$emailJaExiste = $stmtCheck->fetchColumn();

if ($emailJaExiste > 0) {
    http_response_code(403);
    echo json_encode(['status' => 'erro', 'mensagem' => 'E-mail já cadastrado.']);
    exit;
}

$sql = "INSERT INTO cliente(Nome, CPF, Telefone, Email, ativo, EnvioWhatsapp, EnvioEmail) VALUES (";
$sql .= ":nome, :cpf, :telefone, :email, :ativo, :EnvioWhatsapp, :EnvioEmail)";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':nome'          => $nome,
    ':cpf'           => $cpf,
    ':telefone'      => $telefone,
    ':email'         => $email,
    ':ativo'         => $ativo,
    ':EnvioWhatsapp' => $enviowhatsapp,
    ':EnvioEmail'    => $envioemail,
]);

if ($stmt->rowCount() === 1) {
    $userId = $pdo->lastInsertId();
    //echo json_encode(['status' => 'ok', 'userId' => $userId, 'nome' => $nome, 'username' => $userName, 'email' => $email]);
    echo json_encode(['status' => 'ok', 'mensagem' => 'Registro incluido com sucesso.']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao inserir usuário.']);
}

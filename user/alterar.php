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

$userId = $data['userId'] ?? '';
$nome = $data['nome'] ?? '';
$userName = $data['userName'] ?? '';
$telefone = $data['telefone'] ?? '';
$email = $data['email'] ?? '';
$senha = $data['senha'] ?? '';
$perfil = $data['perfil'] ?? '';
$ativo = $data['ativo'] ?? '1'; // true/false para 1/0

// Verificar se o e-mail já está cadastrado
$checkEmailSql = "SELECT COUNT(*) FROM usuario WHERE Email = :email AND Id != :userId";
$stmtCheck = $pdo->prepare($checkEmailSql);
$stmtCheck->execute([':email' => $email, 'userId' => $userId]);
$emailJaExiste = $stmtCheck->fetchColumn();

if ($emailJaExiste > 0) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'E-mail já cadastrado.']);
    exit;
}

$senhaCriptografada = hash('sha256', $senha);

$sql = "UPDATE usuario SET Nome = :nome, UserName = :userName, Telefone = :telefone, pwd = :senha, Email = :email, PerfilId = :PerfilId, Ativo = :ativo";
$sql .= " WHERE Id = :userId";

$stmt = $pdo->prepare($sql);
$stmt->execute([':userId' => $userId,
                ':nome' => $nome, 
                ':userName' => $userName,
                ':telefone' => $telefone,
                ':senha' => $senhaCriptografada,
                ':email' => $email,
                ':PerfilId' => $perfil,
                ':ativo' => $ativo
            ]);

if ($stmt->rowCount() === 1) {
    echo json_encode(['status' => 'ok', 'mensagem' => 'Registro alterado com sucesso.']);
} else {
    http_response_code(500); 
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao alterar usuário.']);
}
<?php
header("Access-Control-Allow-Origin: *"); // ou coloque o domínio exato, ex: http://localhost
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');
require '../DbConnection/conexao.php';
$data = json_decode(file_get_contents('php://input'), true);

$nome = $data['nome'] ?? '';
$userName = $data['userName'] ?? '';
$telefone = $data['telefone'] ?? '';
$email = $data['email'] ?? '';
$senha = $data['senha'] ?? '';
$perfil = $data['perfil'] ?? '';
$ativo = $data['ativo'] ?? '1'; // true/false para 1/0

// Verificar se o e-mail já está cadastrado
$checkEmailSql = "SELECT COUNT(*) FROM usuario WHERE Email = :email";
$stmtCheck = $pdo->prepare($checkEmailSql);
$stmtCheck->execute([':email' => $email]);
$emailJaExiste = $stmtCheck->fetchColumn();

if ($emailJaExiste > 0) {
    http_response_code(403); 
    echo json_encode(['status' => 'erro', 'mensagem' => 'E-mail já cadastrado.']);
    exit;
}

$senhaCriptografada = hash('sha256', $senha);

$sql = "INSERT INTO usuario(Nome, UserName, Telefone, pwd, Email, PerfilId, ativo) VALUES (";
$sql .= ":nome, :userName, :telefone, :senha, :email, :PerfilId, :ativo)";

$stmt = $pdo->prepare($sql);
$stmt->execute([':nome' => $nome, 
                ':userName' => $userName,
                ':telefone' => $telefone,
                ':senha' => $senhaCriptografada,
                ':email' => $email,
                ':PerfilId' => $perfil,
                ':ativo' => $ativo
            ]);

if ($stmt->rowCount() === 1) {
    $userId = $pdo->lastInsertId();
    //echo json_encode(['status' => 'ok', 'userId' => $userId, 'nome' => $nome, 'username' => $userName, 'email' => $email]);
    echo json_encode(['status' => 'ok', 'mensagem' => 'Registro incluido com sucesso.']);
} else {
    http_response_code(500); 
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao inserir usuário.']);
}
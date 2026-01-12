<?php
header('Content-Type: application/json');
require '../DbConnection/conexao.php';
$data = json_decode(file_get_contents('php://input'), true);

$username = $data['userName'] ?? '';
$password = $data['userPwd'] ?? '';


// Apenas para testar (remova depois):
if ($username === '' || $password === '') {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Dados não recebidos corretamente.']);
    exit;
}

$senhaCriptografada = hash('sha256', $password);

$sql = "select 
	u.userId,
    u.nome,
	u.username,
	u.email
from 
	usuario u
WHERE 
    u.email = :username AND u.pwd = :password AND ativo = 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([':username' => $username, ':password' => $senhaCriptografada]);

if ($stmt->rowCount() === 1) {
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    $nome = $resultado['nome'];
    $username   = $resultado['username'];
    $email = $resultado['email'];
    $userId = $resultado['userId'];
    echo json_encode(['status' => 'ok', 'nome' => $nome, 'username' => $username, 'email' => $email, 'userId' => $userId]);
} else {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Usuário ou senha inválidos']);
}

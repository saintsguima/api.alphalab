<?php
header("Access-Control-Allow-Origin: *"); // ou coloque o domínio exato, ex: http://localhost
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');
require '../../DbConnection/connAssessor.php';
$input = json_decode(file_get_contents('php://input'), true);

$nome = $input['Nome'] ?? '';
$cpf = $input['CPF'] ?? '';
$dtnascimento = $input['DtNascimento'] ?? '';
$telefone = $input['Telefone'] ?? '';
$email = $input['Email'] ?? '';
$ativo = $input['Ativo'] ?? '1'; // true/false para 1/0

// Verificar se o e-mail já está cadastrado
$checkEmailSql = "SELECT COUNT(*) FROM Eleitor WHERE email = :email";
$stmtCheck = $pdo->prepare($checkEmailSql);
$stmtCheck->execute([':email' => $email]);
$emailJaExiste = $stmtCheck->fetchColumn();

if ($emailJaExiste > 0) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'E-mail já cadastrado.']);
    exit;
}

$sql = "
    INSERT INTO Eleitor(
        Nome, 
        CPF, 
        DtNascimento, 
        Telefone, 
        Email, 
        Ativo) VALUES (
        :Nome, 
        :CPF, 
        :DtNascimento, 
        :Telefone, 
        :email, 
        :Ativo
        )";


$stmt = $pdo->prepare($sql);
$param =[
    ':Nome' => $nome,
    ':CPF' => $cpf,
    'DtNascimento' => $dtnascimento,
    ':Telefone' => $telefone,
    ':email' => $email,
    ':Ativo' => $ativo
];

$stmt->execute($param);

if ($stmt->rowCount() === 1) {
    $userId = $pdo->lastInsertId();
    //echo json_encode(['status' => 'ok', 'userId' => $userId, 'nome' => $nome, 'username' => $userName, 'email' => $email]);
    echo json_encode(['status' => 'ok', 'mensagem' => 'Registro incluido com sucesso.']);
} else {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao inserir usuário.']);
}
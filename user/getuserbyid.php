<?php
header("Access-Control-Allow-Origin: *"); // ou defina o domínio específico
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

// Após OPTIONS, prossegue para lógica de SELECT
require '../DbConnection/conexao.php';
$data = json_decode(file_get_contents('php://input'), true);

$userId = $data['userId'] ?? '';

$sql = "select 
    u.Nome,
    u.UserName,
    u.Telefone,
    u.Email,
    u.PerfilId,
    u.Ativo
from 
    usuario u
WHERE 
    u.Id = :userId";

$stmt = $pdo->prepare($sql);
$stmt->execute([':userId' => $userId]);

if ($stmt->rowCount() === 1) {
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    $nome = $resultado['Nome'];
    $username = $resultado['UserName'];
    $telefone = $resultado['Telefone'];
    $email = $resultado['Email'];
    $PerfilId = $resultado['PerfilId'];
    $ativo = $resultado['Ativo'];

    echo json_encode([
        'status'   => 'ok',
        'nome'     => $nome,
        'username' => $username,
        'email'    => $email,
        'telefone' => $telefone,
        'userId'   => $userId,
        'PerfilId' => $PerfilId,
        'ativo'    => $ativo
    ]);
} else {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Usuário não existe em nossa base de dados.']);
}
?>
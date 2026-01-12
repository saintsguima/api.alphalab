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

$perfilId = $data['perfilId'] ?? '';

$sql = "SELECT 
	Nome 
FROM 
	Perfil
WHERE 
    Id = :perfilId";

$stmt = $pdo->prepare($sql);
$stmt->execute([':perfilId' => $perfilId]);

if ($stmt->rowCount() === 1) {
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    $Nome = $resultado['Nome']; 

    echo json_encode([
        'status'   => 'ok',
        'Nome' => $Nome
    ]);
} else {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Perfil não existe em nossa base de dados.']);
}
?>
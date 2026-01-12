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

$clienteId = $data['clienteId'] ?? '';

$sql = "SELECT 
	cc.Id, 
	cc.NomeCC, 
    cc.CPFCNPJ
FROM 
	ClienteCC cc
WHERE 
    cc.IdCliente = :clienteId";

$stmt = $pdo->prepare($sql);
$params= [
    ':clienteId' => $clienteId
];

$stmt->execute($params);

if ($stmt->rowCount() > 0) {
    $clienteccs = $stmt->fetchAll(PDO::FETCH_ASSOC); 

    echo json_encode([
        'status' => 'ok',
        'quantidade' => count($clienteccs),
        'clienteccs' => $clienteccs
    ]);

} else {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Cliente Conta Corrente não existe em nossa base de dados.']);
}
?>
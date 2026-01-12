<?php
header("Access-Control-Allow-Origin: *"); // ou coloque o domínio exato, ex: http://localhost
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

// --- TRATAMENTO PARA REQUISIÇÃO OPTIONS (PREFLIGHT) ---
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(); // Sai do script imediatamente!
}
// --------------------------------------------------------

require '../DbConnection/conexao.php';

$data = json_decode(file_get_contents('php://input'), true);

$perfilId = $data['perfilId'] ?? '';



$sql = "SELECT Permissao, flAtivo FROM  PerfilPermissao WHERE PerfilId = :PerfilId ORDER BY Id";


$stmt = $pdo->prepare($sql);
$params =[
    ':PerfilId' => $perfilId
];

$stmt->execute($params);

if ($stmt->rowCount() > 0) {
    $permissoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode([
        'status' => 'ok',
        'quantidade' => count($permissoes),
        'permissoes' => $permissoes
    ]);
} else {
    echo json_encode([
        'status' => 'erro',
        'mensagem' => 'Nenhum Perfil encontrado.'
    ]);
}
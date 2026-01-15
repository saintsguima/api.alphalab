<?php
header("Access-Control-Allow-Origin: *"); // ou coloque o domínio exato, ex: http://localhost
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');
require '../DbConnection/conexao.php';

$sql = "select
	Id,
    Nome
from
	Plano";

$stmt = $pdo->prepare($sql);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    $plano = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode([
        'status'     => 'ok',
        'quantidade' => count($plano),
        'plano'      => $plano,
    ]);
} else {
    echo json_encode([
        'status'   => 'erro',
        'mensagem' => 'Nenhum Plano encontrado.',
    ]);
}

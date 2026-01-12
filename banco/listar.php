<?php
header("Access-Control-Allow-Origin: *"); // ou coloque o domínio exato, ex: http://localhost
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');
require '../DbConnection/conexao.php';

$sql = "select 
	Id,
	CONCAT (Codigo, ' - ' , Nome) Nome
from 
	banco";


$stmt = $pdo->prepare($sql);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    $bancos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode([
        'status' => 'ok',
        'quantidade' => count($bancos),
        'bancos' => $bancos
    ]);
} else {
    echo json_encode([
        'status' => 'erro',
        'mensagem' => 'Nenhum banco encontrado.'
    ]);
}
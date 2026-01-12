<?php
header("Access-Control-Allow-Origin: *"); // ou coloque o domínio exato, ex: http://localhost
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');
require '../DbConnection/conexao.php';

$sql = "
        SELECT 	
            Id,
            Nome,
            CPFCNPJ,
            Valor,
            Data
        FROM
            Extratos
        WHERE 
            Conciliado = 0
        ";


$stmt = $pdo->prepare($sql);

$stmt->execute();

if ($stmt->rowCount() > 0) {
    $nis = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode([
        'status' => 'ok',
        'quantidade' => count($nis),
        'nis' => $nis
    ]);
} else {
    echo json_encode([
        'status' => 'erro',
        'mensagem' => 'Nenhuma Extrato encontrado para o cliente.'
    ]);
}
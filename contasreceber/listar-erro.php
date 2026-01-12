<?php
header("Access-Control-Allow-Origin: *"); // ou coloque o domínio exato, ex: http://localhost
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');
require '../DbConnection/conexao.php';

$sql = "SELECT
	Id,
    NomeArquivo,
    Linha,
    CPFCNPJ,
    Historico,
    Valor,
    DtInicio,
    DtFinal,
    DtCriacao
FROM
	dlCargaCliente";

$stmt = $pdo->prepare($sql);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    $crerros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode([
        'status'     => 'ok',
        'quantidade' => count($crerros),
        'crerros'    => $crerros,
    ]);
} else {
    echo json_encode([
        'status'   => 'erro',
        'mensagem' => 'Nenhuma listagem de erros encontrado.',
    ]);
}

<?php
header("Access-Control-Allow-Origin: *"); // ou coloque o domínio exato, ex: http://localhost
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');
require '../DbConnection/conexao.php';

$sql = "SELECT 
	cr.Id, 
    cr.IdCliente,
    cl.Nome,
    cr.DtInicio,
    cr.DtFinal,
    cr.VlTotal,
    cr.VlConciliado,
    cr.DtCC,
    cr.IdUsuarioInclusao,
    cr.Ativo 
FROM 
	ContasReceber cr
inner join cliente cl on cl.id = cr.IdCliente";


$stmt = $pdo->prepare($sql);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    $crs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode([
        'status' => 'ok',
        'quantidade' => count($crs),
        'crs' => $crs
    ]);
} else {
    echo json_encode([
        'status' => 'erro',
        'mensagem' => 'Nenhuma Conta a Receber encontradoa.'
    ]);
}
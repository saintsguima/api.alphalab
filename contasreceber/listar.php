<?php
header("Access-Control-Allow-Origin: *"); // ou coloque o domínio exato, ex: http://localhost
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');
require '../DbConnection/conexao.php';

$data = json_decode(file_get_contents('php://input'), true);

$Id        = $data["Id"] ?? '';
$dtInicial = $data["dtInicial"] ?? '';
$dtFinal   = $data["dtFinal"] ?? '';

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
INNER JOIN cliente cl ON cl.id = cr.IdCliente
WHERE
    cr.DtFinal >= :dataInicial
    AND cr.DtFinal <= :dataFinal ";

if ($Id > 0) {
    $sql .= "AND cr.IdCliente = :IdCliente ";
}

$sql .= "
ORDER BY
    cr.DtFinal DESC";

$stmt = $pdo->prepare($sql);

if ($Id > 0) {
    $params = [
        ':IdCliente'   => $Id,
        ':dataInicial' => $dtInicial,
        ':dataFinal'   => $dtFinal,
    ];
} else {
    $params = [
        ':dataInicial' => $dtInicial,
        ':dataFinal'   => $dtFinal,
    ];
}

$stmt->execute($params);

if ($stmt->rowCount() > 0) {
    $crs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode([
        'status'     => 'ok',
        'quantidade' => count($crs),
        'crs'        => $crs,
    ]);
} else {
    echo json_encode([
        'status'   => 'erro',
        'mensagem' => 'Nenhuma Conta a Receber encontradoa.',
    ]);
}

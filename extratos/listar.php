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
$sql       = "
        SELECT
            ec.Id,
            ec.IdCliente,
            cl.Nome,
            ec.Data,
            ec.Historico,
            ec.Credito,
            ec.Debito,
            ec.Saldo
        FROM
            ExtratoCliente ec
        INNER JOIN cliente cl ON cl.Id = ec.IdCliente
        WHERE
            ec.IdCliente = :IdCliente
            AND ec.Data >= :dataInicial
            AND ec.Data <= :dataFinal
        ORDER BY
            ec.Id DESC
";

$stmt = $pdo->prepare($sql);

$params = [
    ':IdCliente'   => $Id,
    ':dataInicial' => $dtInicial,
    ':dataFinal'   => $dtFinal,
];

$stmt->execute($params);

if ($stmt->rowCount() > 0) {
    $ecs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode([
        'status'     => 'ok',
        'quantidade' => count($ecs),
        'ecs'        => $ecs,
    ]);
} else {
    echo json_encode([
        'status'   => 'erro',
        'mensagem' => 'Nenhuma Extrato encontrado para o cliente.',
    ]);
}

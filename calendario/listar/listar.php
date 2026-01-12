<?php
header("Access-Control-Allow-Origin: *"); // ou coloque o domínio exato, ex: http://localhost
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');
require '../../DbConnection/connAssessor.php'; 

$input = json_decode(file_get_contents('php://input'), true);

$startDate = $input['startDate'];
$endDate = $input['endDate'];
$userId = $input['userId'];

$params = [];

// Total de registros sem filtro
$sql = "
    SELECT
    a.Id,
    CASE
        WHEN a.IdTipoAgenda = 1 
        THEN CONCAT('De ', DATE_FORMAT(a.DtInicio, '%H:%i:%s'), ' até ', DATE_FORMAT(a.DtFinal, '%H:%i:%s'), ' - ', a.Titulo)
        ELSE a.Titulo
    END AS Titulo,    CASE 
        WHEN a.IdTipoAgenda = 1 
        THEN DATE_FORMAT(a.DtInicio, '%Y-%m-%d')
        ELSE DATE_FORMAT(a.DtInicio, '%Y-%m-%dT%H:%i:%s')
    END AS DtInicio,
    CASE 
        WHEN a.IdTipoAgenda = 1 
        THEN DATE_FORMAT(a.DtFinal,  '%Y-%m-%d')
        ELSE DATE_FORMAT(a.DtFinal,  '%Y-%m-%dT%H:%i:%s')
    END AS DtFinal,
    a.FlAllDaty,
    a.IdTipoAgenda,
    ta.Descricao,
    a.FlAtivo
    FROM Agenda a
    JOIN TipoAgenda ta ON ta.Id = a.IdTipoAgenda
    WHERE
    (a.DtFinal  > :startDate OR a.DtFinal IS NULL)  -- evento termina depois do início (ou sem fim)
    AND a.DtInicio < :endDate                      -- e começa antes do fim
    AND a.IdUser = :userId;
";

$stmt = $pdo->prepare($sql);
$param = [
    ':userId' => $userId,
    ':startDate' => $startDate,
    ':endDate' => $endDate
];

$stmt->execute($param);

if ($stmt->rowCount() > 0) {
    $calendario = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode([
        'status' => 'ok',
        'quantidade' => count($calendario),
        'calendario' => $calendario
    ]);
} else {
    echo json_encode([
        'status' => 'erro',
        'mensagem' => 'Nenhum Agendamento.'
    ]);
}
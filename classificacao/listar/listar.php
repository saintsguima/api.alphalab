<?php
header('Content-Type: application/json');
require '../../DbConnection/connPharma,php';

$sql = "select classificacaoId, nomeClassificacao, codigoClassificacao, flAtivo from classificacao";

$stmt = $pdo->prepare($sql);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    $classificacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode([
        'status' => 'ok',
        'quantidade' => count($classificacoes),
        'classificacoes' => $classificacoes
    ]);
} else {
    echo json_encode([
        'status' => 'erro',
        'mensagem' => 'Nenhuma Classificação encontrada.'
    ]);
}
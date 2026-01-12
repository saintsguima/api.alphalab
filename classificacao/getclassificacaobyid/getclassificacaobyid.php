<?php
header('Content-Type: application/json');
require '../../DbConnection/connPharma,php'; 
$data = json_decode(file_get_contents('php://input'), true);

$classificacaoId = $data['classificacaoId'] ?? '';

$sql = "select 
    c.nomeClassificacao,
	c.codigoClassificacao,
    c.classificacaoId,
    c.flAtivo
from 
	classificacao c
WHERE 
    c.classificacaoId = :classificacaoId";
$stmt = $pdo->prepare($sql);
$stmt->execute([':classificacaoId' => $classificacaoId]);

if ($stmt->rowCount() === 1) {
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    $nomeClassificacao = $resultado['nomeClassificacao'];
    $codigoClassificacao  = $resultado['codigoClassificacao'];
    $flAtivo = $resultado['flAtivo'];

    echo json_encode(['status' => 'ok', 'nomeClassificacao' => $nomeClassificacao, 'codigoClassificacao' => $codigoClassificacao, 'flAtivo' => $flAtivo]);
} else {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Classificação não existe em nossa base de dados.']);
}

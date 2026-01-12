<?php
header('Content-Type: application/json');
require '../../DbConnection/connPharma,php'; 
$data = json_decode(file_get_contents('php://input'), true);

$classificacaoId = $data['classificacaoId'] ?? '';
$nomeClassificacao = $data['nomeClassificacao'] ?? '';
$codigoClassificacao = $data['codigoClassificacao'] ?? '0';
$flAtivo = $data['flAtivo'] ?? '0';
// Verificar se o e-mail já está cadastrado
$checkClassificacaoSql = "SELECT COUNT(*) FROM classificacao WHERE codigoClassificacao = :codigoClassificacao and classificacaoId != :classificacaoId";
$stmtCheck = $pdo->prepare($checkClassificacaoSql);
$stmtCheck->execute([':codigoClassificacao' => $codigoClassificacao, ':classificacaoId' => $classificacaoId]);
$classificacaoJaExiste = $stmtCheck->fetchColumn();

if ($classificacaoJaExiste > 0) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Código da Classificação já cadastrado.']);
    exit;
}


$sql = "UPDATE classificacao SET nomeClassificacao = :nomeClassificacao, flAtivo = :flAtivo";
$sql .= " WHERE classificacaoId = :classificacaoId";

$stmt = $pdo->prepare($sql);
$stmt->execute([':nomeClassificacao' => $nomeClassificacao,
                ':flAtivo' => $flAtivo,
                ':classificacaoId' => $classificacaoId
            ]);

if ($stmt->rowCount() === 1) {
    echo json_encode(['status' => 'ok', 'mensagem' => 'Registro alterado com sucesso.']);
} else {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao alterar Classificação.']);
}
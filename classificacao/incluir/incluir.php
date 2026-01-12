<?php
header('Content-Type: application/json');
require '../../DbConnection/connPharma,php'; 
$data = json_decode(file_get_contents('php://input'), true);

$nomeClassificacao = $data['nomeClassificacao'] ?? '';
$codigoClassificacao = $data['codigoClassificacao'] ?? '0';
$flAtivo = $data['flAtivo'] ?? '0';
// Verificar se o e-mail já está cadastrado
$checkClassificacaoSql = "SELECT COUNT(*) FROM classificacao WHERE codigoClassificacao = :codigoClassificacao";
$stmtCheck = $pdo->prepare($checkClassificacaoSql);
$stmtCheck->execute([':codigoClassificacao' => $codigoClassificacao]);
$classificacaoJaExiste = $stmtCheck->fetchColumn();

if ($classificacaoJaExiste > 0) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Código da Classificação já cadastrado.']);
    exit;
}


$sql = "INSERT INTO classificacao (codigoClassificacao, nomeClassificacao, flAtivo) values (:codigoClassificacao, :nomeClassificacao, :flAtivo)";

$stmt = $pdo->prepare($sql);
$stmt->execute([':codigoClassificacao' => $codigoClassificacao, 
                ':nomeClassificacao' => $nomeClassificacao,
                ':flAtivo' => $flAtivo
            ]);

if ($stmt->rowCount() === 1) {
    echo json_encode(['status' => 'ok', 'mensagem' => 'Registro incluido com sucesso.']);
} else {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao incluir Classificação.']);
}
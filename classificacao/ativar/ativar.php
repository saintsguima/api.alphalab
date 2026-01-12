<?php
header('Content-Type: application/json');
require '../../DbConnection/connPharma,php'; 
$data = json_decode(file_get_contents('php://input'), true);

$classificacaoId = $data['classificacaoId'] ?? '';
$ativo = $data['ativo'] ?? '1'; // true/false para 1/0


$sql = "UPDATE classificacao SET flAtivo = :ativo WHERE classificacaoId = :classificacaoId";

$stmt = $pdo->prepare($sql);
$stmt->execute([':classificacaoId' => $classificacaoId, 
                ':ativo' => $ativo
            ]);

if ($stmt->rowCount() === 1) {
    $classificacaoId = $pdo->lastInsertId();
    echo json_encode(['status' => 'ok', 'mensagem' => 'Ação executada com sucesso.']);
} else {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao executar a ação.']);
}
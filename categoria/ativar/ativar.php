<?php
header('Content-Type: application/json');
require '../../DbConnection/connPharma,php'; 
$data = json_decode(file_get_contents('php://input'), true);

$categoriaId = $data['categoriaId'] ?? '';
$ativo = $data['ativo'] ?? '1'; // true/false para 1/0


$sql = "UPDATE categoria SET flAtivo = :ativo WHERE categoriaId = :categoriaId";

$stmt = $pdo->prepare($sql);
$stmt->execute([':categoriaId' => $categoriaId, 
                ':ativo' => $ativo
            ]);

if ($stmt->rowCount() === 1) {
    $categoriaId = $pdo->lastInsertId();
    echo json_encode(['status' => 'ok', 'mensagem' => 'Ação executada com sucesso.']);
} else {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao executar a ação.']);
}
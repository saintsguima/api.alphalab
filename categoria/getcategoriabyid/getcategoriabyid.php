<?php
header('Content-Type: application/json');
require '../../DbConnection/connPharma,php'; 
$data = json_decode(file_get_contents('php://input'), true);

$categoriaId = $data['categoriaId'] ?? '';

$sql = "select 
    c.nomeCategoria,
	c.codigoCategoria,
    c.categoriaId,
    c.flAtivo
from 
	categoria c
WHERE 
    c.categoriaId = :categoriaId";
$stmt = $pdo->prepare($sql);
$stmt->execute([':categoriaId' => $categoriaId]);

if ($stmt->rowCount() === 1) {
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    $nomeCategoria = $resultado['nomeCategoria'];
    $codigoCategoria   = $resultado['codigoCategoria'];
    $flAtivo = $resultado['flAtivo'];

    echo json_encode(['status' => 'ok', 'nomeCategoria' => $nomeCategoria, 'codigoCategoria' => $codigoCategoria, 'flAtivo' => $flAtivo]);
} else {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Categoria não existe em nossa base de dados.']);
}

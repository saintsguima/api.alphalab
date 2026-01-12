<?php
header('Content-Type: application/json');
require '../../DbConnection/connPharma,php';

$sql = "select categoriaId, nomeCategoria, codigoCategoria, flAtivo from categoria";

$stmt = $pdo->prepare($sql);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode([
        'status' => 'ok',
        'quantidade' => count($categorias),
        'categorias' => $categorias
    ]);
} else {
    echo json_encode([
        'status' => 'erro',
        'mensagem' => 'Nenhum categoria encontrada.'
    ]);
}
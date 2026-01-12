<?php
header('Content-Type: application/json');
require '../../DbConnection/connPharma,php'; 
$data = json_decode(file_get_contents('php://input'), true);

$categoriaId = $data['categoriaId'] ?? '';
$nomeCategoria = $data['nomeCategoria'] ?? '';
$codigoCategoria = $data['codigoCategoria'] ?? '0';
$flAtivo = $data['flAtivo'] ?? '0';

// Verificar se o e-mail já está cadastrado
$checkCategoriaSql = "SELECT COUNT(*) FROM categoria WHERE codigoCategoria = :codigoCategoria and categoriaId != :categoriaId";
$stmtCheck = $pdo->prepare($checkCategoriaSql);
$stmtCheck->execute([':codigoCategoria' => $codigoCategoria, ':categoriaId' => $categoriaId]);
$categoriaJaExiste = $stmtCheck->fetchColumn();

if ($categoriaJaExiste > 0) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Código da Categoria já cadastrado.']);
    exit;
}


$sql = "UPDATE categoria SET nomeCategoria = :nomeCategoria, flAtivo = :flAtivo";
$sql .= " WHERE categoriaId = :categoriaId";

$stmt = $pdo->prepare($sql);
$stmt->execute([':nomeCategoria' => $nomeCategoria,
                ':flAtivo' => $flAtivo,
                ':categoriaId' => $categoriaId
            ]);

if ($stmt->rowCount() === 1) {
    echo json_encode(['status' => 'ok', 'mensagem' => 'Registro alterado com sucesso.']);
} else {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao alterar Categoria.']);
}
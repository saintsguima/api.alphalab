<?php
header('Content-Type: application/json');
require '../../DbConnection/connPharma,php'; 
$data = json_decode(file_get_contents('php://input'), true);

$nomeCategoria = $data['nomeCategoria'] ?? '';
$codigoCategoria = $data['codigoCategoria'] ?? '0';
$flAtivo = $data['flAtivo'] ?? '0';

// Verificar se o e-mail já está cadastrado
$checkGrupoSql = "SELECT COUNT(*) FROM categoria WHERE codigoCategoria = :codigoCategoria";
$stmtCheck = $pdo->prepare($checkGrupoSql);
$stmtCheck->execute([':codigoCategoria' => $codigoCategoria]);
$categoriaJaExiste = $stmtCheck->fetchColumn();

if ($categoriaJaExiste > 0) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Código da Categoria já cadastrado.']);
    exit;
}


$sql = "INSERT INTO categoria (codigoCategoria, nomeCategoria, flAtivo) values (:codigoCategoria, :nomeCategoria, :flAtivo)";

$stmt = $pdo->prepare($sql);
$stmt->execute([':codigoCategoria' => $codigoCategoria, 
                ':nomeCategoria' => $nomeCategoria,
                ':flAtivo' => $flAtivo
            ]);

if ($stmt->rowCount() === 1) {
    echo json_encode(['status' => 'ok', 'mensagem' => 'Registro incluido com sucesso.']);
} else {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao incluir Categoria.']);
}
<?php
header('Content-Type: application/json');
require '../../DbConnection/connPharma,php'; 

$input = json_decode(file_get_contents('php://input'), true);

$produtoCodigo = $input['produtoCodigo'] ?? '';


// Consulta paginada
$sql = "select 
	p.produtoId,
	p.codigo, 
	p.nome, 
	p.valorVenda, 
	p.valorCusto, 
	p.valorCustoMedio, 
	p.quantidadeEstoque, 
	p.unidade, 
	p.codigoBarras, 
	p.codigoLaboratorio, 
	p.nomeLaboratorio, 
	p.codigoGrupo, 
	p.nomeGrupo, 
	p.codigoCategoria, 
	p.nomeCategoria, 
	p.codigoClassificacao, 
	p.nomeClassificacao, 
	p.ativo, 
	p.percentualDesconto,
	p.observacaoVenda,
	GROUP_CONCAT(CONCAT(t.tagId, ' - ' , t.nome) ORDER BY t.nome SEPARATOR ' | ') AS tags
FROM 
	produto p
LEFT JOIN produto_tag pt ON pt.produtoId = p.produtoId
LEFT JOIN tag t ON t.tagId = pt.tagId
where
	codigo = :codigo
GROUP BY p.produtoId";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':codigo', $produtoCodigo);
$stmt->execute();

if ($stmt->rowCount() === 1){
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'ok',
        'mensagem' => 'registro encontrado.',
        'data' => $resultado
    ]);
}else {
    echo json_encode([
        'status' => 'erro',
        'mensagem' => 'nenhum produto encontrado.'
    ]);
}
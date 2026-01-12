<?php
header('Content-Type: application/json');
require '../../DbConnection/connPharma,php';
$data = json_decode(file_get_contents('php://input'), true);

$produtoId = $data["produtoId"] ?? 0;
$codigo = $data["codigo"] ?? 0;
$nome = $data["nome"] ?? '';
$codigoLaboratorio = $data["codigoLaboratorio"] ?? 0;
$nomeLaboratorio = $data["nomeLaboratorio"] ?? '';
$codigoGrupo = $data["codigoGrupo"] ?? 0;
$nomeGrupo = $data["nomeGrupo"] ?? '';
$codigoCategoria = $data["codigoCategoria"] ?? 0;
$nomeCategoria = $data["nomeCategoria"] ?? '';
$codigoClassificacao = $data["codigoClassificacao"] ?? 0;
$nomeClassificacao = $data["nomeClassificacao"] ?? '';
$quantidade = $data["quantidade"] ?? 0;
$unidade = $data["unidade"] ?? '';
$codigoBarras = $data["codigoBarras"] ?? 0;
$valorVenda = $data["valorVenda"] ?? 0;
$percentualDesconto = $data["percentualDesconto"] ?? 0;
$valorCusto = $data["valorCusto"] ?? 0;
$valorCustoMedio = $data["valorCustoMedio"] ?? 0;
$observacao = $data["observacao"] ?? '';
$ativo = $data["ativo"] ?? 1;
$tag = $data["tag"] ?? [];

$sql = "UPDATE 
	produto
SET
	nome = :nome, 
	valorVenda = :valorVenda, 
	valorCusto = :valorCusto, 
	valorCustoMedio = :valorCustoMedio, 
	quantidadeEstoque = :quantidadeEstoque, 
	unidade = :unidade, 
	codigoBarras = :codigoBarras, 
	codigoLaboratorio = :codigoLaboratorio, 
	nomeLaboratorio = :nomeLaboratorio, 
	codigoGrupo = :codigoGrupo, 
	nomeGrupo = :nomeGrupo, 
	codigoCategoria = :codigoCategoria, 
	nomeCategoria = :nomeCategoria, 
	codigoClassificacao = :codigoClassificacao, 
	nomeClassificacao = :nomeClassificacao, 
	ativo = :ativo, 
	percentualDesconto = :percentualDesconto,
	observacaoVenda = :observacaoVenda 
WHERE
	codigo = :codigo ";

$stmt = $pdo->prepare($sql);
$stmt->execute([':codigo' => $codigo,
                ':nome' => $nome,
                ':valorVenda' => $valorVenda,
                ':valorCusto' => $valorCusto,
                ':valorCustoMedio' => $valorCustoMedio,
                ':quantidadeEstoque' => $quantidade,
                ':unidade' => $unidade,
                ':codigoBarras' => $codigoBarras,
                ':codigoLaboratorio' => $codigoLaboratorio,
                ':nomeLaboratorio' => $nomeLaboratorio,
                ':codigoGrupo' => $codigoGrupo,
                ':nomeGrupo' => $nomeGrupo,
                ':codigoCategoria' => $codigoCategoria,
                ':nomeCategoria' => $nomeCategoria,
                ':codigoClassificacao' => $codigoClassificacao,
                ':nomeClassificacao' => $nomeClassificacao,
                ':ativo' => $ativo,
                ':percentualDesconto' => $percentualDesconto,
                ':observacaoVenda' => $observacao
            ]);

    deleteTags($pdo, $produtoId);
    insertTag($pdo, $tag, $produtoId);

    echo json_encode(['status' => 'ok', 'mensagem' => 'Registro alterado com sucesso.']);

function deleteTags($pdo, $produtoId) {

    $sql = "DELETE FROM produto_tag WHERE produtoId = :produtoId";
    $stmt = $pdo->prepare($sql);

    $stmt->execute([':produtoId' => $produtoId]);
}

function insertTag($pdo, $tags, $produtoId) {
    if (empty($tags)) return;

    $sql = "INSERT INTO produto_tag (produtoId, tagId) VALUES (:produtoId, :tagId)";
    $stmt = $pdo->prepare($sql);

    foreach ($tags as $tagId) {
        $stmt->execute([
            ':produtoId' => $produtoId,
            ':tagId' => $tagId
        ]);
    }
}

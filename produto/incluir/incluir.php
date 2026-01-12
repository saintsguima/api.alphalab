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

// Verificar se o e-mail já está cadastrado
$checkCodigoProdutoSql = "SELECT COUNT(*) FROM produto WHERE codigo = :codigo";
$stmtCheck = $pdo->prepare($checkCodigoProdutoSql);
$stmtCheck->execute([':codigo' => $codigo]);
$codigoJaExiste = $stmtCheck->fetchColumn();

if ($codigoJaExiste > 0) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Código já cadastrado.']);
    exit;
}


$sql = "INSERT INTO produto(
	codigo, 
	nome, 
	valorVenda, 
	valorCusto, 
	valorCustoMedio, 
	quantidadeEstoque, 
	unidade, 
	codigoBarras, 
	codigoLaboratorio, 
	nomeLaboratorio, 
	codigoGrupo, 
	nomeGrupo, 
	codigoCategoria, 
	nomeCategoria, 
	codigoClassificacao, 
	nomeClassificacao, 
	ativo, 
	percentualDesconto,
	observacaoVenda 
) VALUES (
	:codigo,
	:nome,
	:valorVenda,
	:valorCusto,
	:valorCustoMedio,
	:quantidade,
	:unidade,
	:codigoBarras,
	:codigoLaboratorio,
	:nomeLaboratorio,
	:codigoGrupo,
	:nomeGrupo,
	:codigoCategoria,
	:nomeCategoria,
	:codigoClassificacao,
	:nomeClassificacao,
	:ativo,
	:percentualDesconto,
	:observacao
)";

$stmt = $pdo->prepare($sql);
$stmt->execute([':codigo' => $codigo,
                ':nome' => $nome,
                ':valorVenda' => $valorVenda,
                ':valorCusto' => $valorCusto,
                ':valorCustoMedio' => $valorCustoMedio,
                ':quantidade' => $quantidade,
                ':codigo' => $codigo,
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
                ':observacao' => $observacao
            ]);

if ($stmt->rowCount() === 1) {
    $produtoId = $pdo->lastInsertId();
    insertTag($pdo, $tag, $produtoId);
    //echo json_encode(['status' => 'ok', 'userId' => $userId, 'nome' => $nome, 'username' => $userName, 'email' => $email]);
    echo json_encode(['status' => 'ok', 'mensagem' => 'Registro incluido com sucesso.']);
} else {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao inserir Produto.']);
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

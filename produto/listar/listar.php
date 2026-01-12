<?php
header('Content-Type: application/json');
require '../../DbConnection/connPharma,php'; 

$input = json_decode(file_get_contents('php://input'), true);

$draw = $input['draw'] ?? 1;
$start = $input['start'] ?? 1;
$length = $input['length'] ?? 10;
$searchTerm = $input['searchTerm'] ?? '';

$where = '';
$params = [];

if (!empty($searchTerm)) {
    $where = "WHERE p.nome LIKE :filtro OR t.nome LIKE :filtro";
    $params[':filtro'] = "%" . $searchTerm . "%";
} 

// Total de registros sem filtro
$sqlTotal = "SELECT COUNT(DISTINCT p.produtoId)
    FROM produto p
    LEFT JOIN produto_tag pt ON pt.produtoId = p.produtoId
    LEFT JOIN tag t ON t.tagId = pt.tagId
    $where";
$stmtTotal = $pdo->prepare($sqlTotal);
foreach ($params as $key => $value) {
    $stmtTotal->bindValue($key, $value);
}
$stmtTotal->execute();
$totalRecords = $stmtTotal->fetchColumn();

// Consulta paginada
$sql = "SELECT 
    p.codigo,
    p.produtoId,
    p.nome,
    p.valorVenda,
    p.quantidadeEstoque,
    p.nomeLaboratorio,
    p.nomeGrupo,
    p.nomeCategoria,
    p.nomeClassificacao,
    p.ativo,
    GROUP_CONCAT(t.nome ORDER BY t.nome SEPARATOR ' | ') AS tags
FROM produto p
LEFT JOIN produto_tag pt ON pt.produtoId = p.produtoId
LEFT JOIN tag t ON t.tagId = pt.tagId
$where
GROUP BY p.produtoId
ORDER BY p.nome ASC
LIMIT :start, :length";


//GROUP_CONCAT(CONCAT(t.tagId, ' - ' , t.nome) ORDER BY t.nome SEPARATOR ' | ') AS tags
$stmt = $pdo->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':start', (int)$start, PDO::PARAM_INT);
$stmt->bindValue(':length', (int)$length, PDO::PARAM_INT);
$stmt->execute();
$dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'draw' => intval($draw),
    'recordsTotal' => intval($totalRecords),
    'recordsFiltered' => intval($totalRecords), // caso queira separar depois, mude aqui
    'data' => $dados
]);

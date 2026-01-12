<?php
header("Access-Control-Allow-Origin: *"); // ou coloque o domínio exato, ex: http://localhost
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');
require '../../DbConnection/connAssessor.php'; 
$input = json_decode(file_get_contents('php://input'), true);


$draw = $input['draw'] ?? 1;
$start = $input['start'] ?? 1;
$length = $input['length'] ?? 10;
$searchTerm = $input['searchTerm'] ?? '';

$where = '';
$params = [];

if (!empty($searchTerm)) {
    $where = "WHERE Nome LIKE :filtro OR CPF LIKE :filtro";
    $params[':filtro'] = "%" . $searchTerm . "%";
} 

// Total de registros sem filtro
$sqlTotal = "SELECT COUNT(*)
    FROM Eleitor
    $where";
$stmtTotal = $pdo->prepare($sqlTotal);
foreach ($params as $key => $value) {
    $stmtTotal->bindValue($key, $value);
}
$stmtTotal->execute();
$totalRecords = $stmtTotal->fetchColumn();

// Consulta paginada
$sql = "
    SELECT 
        Id, 
        Nome, 
        CPF,
        DtNascimento, 
        Telefone, 
        email, 
        Ativo 
    FROM 
        Eleitor
$where

ORDER BY Nome ASC
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

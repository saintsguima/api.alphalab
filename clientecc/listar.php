<?php
header("Access-Control-Allow-Origin: *"); // ou coloque o domínio exato, ex: http://localhost
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');
require '../DbConnection/conexao.php';

$sql = "SELECT
	cc.Id,
	cc.IdCliente,
	cl.nome NomeCliente,
	cc.NomeCC,
	cc.IdBanco,
	CONCAT (bc.codigo, ' - ', bc.nome) NomeBanco,
	cc.Agencia,
	cc.CC,
	CASE cc.TipoChavePix
		WHEN 1 THEN 'CPF / CNPJ'
		WHEN 2 THEN 'EMAIL'
		WHEN 3 THEN 'TELEFONE'
		WHEN 4 THEN 'CHAVE ALEATÓRIA'
		ELSE '' END TipoChavePix,
	fmt_cpf_cnpj(cc.CPFCNPJ) CPFCNPJ,
	cc.ChavePix
FROM
	ClienteCC cc
LEFT JOIN banco bc on bc.id = cc.IdBanco
INNER JOIN cliente cl on cl.id = cc.IdCliente ";

$stmt = $pdo->prepare($sql);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    $clienteccs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode([
        'status'     => 'ok',
        'quantidade' => count($clienteccs),
        'clienteccs' => $clienteccs,
    ]);
} else {
    echo json_encode([
        'status'   => 'erro',
        'mensagem' => 'Nenhuma Conta Corrente encontrado.',
    ]);
}

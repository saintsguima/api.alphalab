<?php
header("Access-Control-Allow-Origin: *"); // ou defina o domínio específico
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

// RESPOSTA AO PRE-FLIGHT CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Opcional: defina tempo de cache do preflight
    header('Access-Control-Max-Age: 86400');
    http_response_code(200);
    exit();
}

// Após OPTIONS, prossegue para lógica de SELECT
require '../DbConnection/conexao.php';
$data = json_decode(file_get_contents('php://input'), true);

$clienteId = $data['clienteId'] ?? '';

$sql = "SELECT
	cc.Id,
	cc.IdCliente,
	cl.nome NomeCliente,
	cc.NomeCC,
	cc.IdBanco,
	CONCAT (bc.codigo, ' - ', bc.nome) NomeBanco,
	cc.Agencia,
	cc.CC,
    cc.CPFCNPJ,
	CASE cc.TipoChavePix
		WHEN 1 THEN 'CPF / CNPJ'
		WHEN 2 THEN 'Email'
		WHEN 3 THEN 'Telefone'
		WHEN 4 THEN 'Chave Aleatória'
		ELSE '' END TipoChavePix,
	cc.ChavePix,
    cc.Ativo
FROM
	ClienteCC cc
LEFT JOIN banco bc on bc.id = cc.IdBanco
INNER JOIN cliente cl on cl.id = cc.IdCliente
WHERE
    cc.Id = :clienteId";

$stmt = $pdo->prepare($sql);
$stmt->execute([':clienteId' => $clienteId]);

if ($stmt->rowCount() === 1) {
    $resultado    = $stmt->fetch(PDO::FETCH_ASSOC);
    $Id           = $resultado['Id'];
    $IdCliente    = $resultado['IdCliente'];
    $NomeCliente  = $resultado['NomeCliente'];
    $NomeCC       = $resultado['NomeCC'];
    $IdBanco      = $resultado['IdBanco'];
    $NomeBanco    = $resultado['NomeBanco'];
    $Agencia      = $resultado['Agencia'];
    $CC           = $resultado['CC'];
    $CPFCNPJ      = $resultado['CPFCNPJ'];
    $TipoChavePix = $resultado['TipoChavePix'];
    $ChavePix     = $resultado['ChavePix'];
    $Ativo        = $resultado['Ativo'];

    echo json_encode([
        'status'       => 'ok',
        'Id'           => $Id,
        'IdCliente'    => $IdCliente,
        'NomeCliente'  => $NomeCliente,
        'NomeCC'       => $NomeCC,
        'IdBanco'      => $IdBanco,
        'NomeBanco'    => $NomeBanco,
        'Agencia'      => $Agencia,
        'CC'           => $CC,
        'CPFCNPJ'      => $CPFCNPJ,
        'TipoChavePix' => $TipoChavePix,
        'ChavePix'     => $ChavePix,
        'Ativo'        => $Ativo,
    ]);
} else {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Cliente Conta Corrente não existe em nossa base de dados.']);
}

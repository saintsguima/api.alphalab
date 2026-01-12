<?php
header("Access-Control-Allow-Origin: *"); // ou coloque o domínio exato, ex: http://localhost
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

require '../DbConnection/conexao.php';

// Função para limpar pontuação
function onyNumber(string $valor): string {
    return preg_replace('/\D/', '', $valor);
}


$data = json_decode(file_get_contents('php://input'), true);

$Id = $data['Id'] ?? '';
$IdCliente = $data['IdCliente'] ?? '';
$NomeCC = $data['NomeCC'] ?? '';
$IdBanco = $data['IdBanco'] ?? '';
$Agencia = $data['Agencia'] ?? '';
$CC = $data['CC'] ?? '';
$CPFCNPJ = isset($data['CPFCNPJ']) ? onyNumber($data['CPFCNPJ']) : '';
$TipoChavePix = $data['TipoChavePix'] ?? '';
$ChavePix = $data['ChavePix'] ?? '';
$Ativo = $data['Ativo'] ?? '1'; // true/false para 1/0

// Verificar se o e-mail já está cadastrado
$checkSql = "SELECT COUNT(*) FROM ClienteCC WHERE IdBanco = :IdBanco and Agencia = :Agencia and CC = :CC and Id != :Id";
$stmtCheck = $pdo->prepare($checkSql);
$stmtCheck->execute([':IdBanco' => $IdBanco, ':Agencia' => $Agencia, ':CC' => $CC, ':Id' => $Id]);
$JaExiste = $stmtCheck->fetchColumn();

if ($JaExiste > 0) {
    http_response_code(403); 
    echo json_encode(['status' => 'erro', 'mensagem' => 'Banco, Agência e Conta Corrente já cadastrado.']);
    exit;
}

$sql = "UPDATE 
	ClienteCC 
SET
    IdCliente = :IdCliente,
    NomeCC = :NomeCC,
    IdBanco = :IdBanco,
    Agencia = :Agencia,
    CC = :CC,
    CPFCNPJ = :CPFCNPJ,
    TipoChavePix = :TipoChavePix,
    ChavePix = :ChavePix,
    Ativo = :Ativo
WHERE
	Id = :Id";

$stmt = $pdo->prepare($sql);
$stmt->execute([
   ':IdCliente' => $IdCliente,
   ':NomeCC' => $NomeCC,
   ':IdBanco' => $IdBanco,
   ':Agencia' => $Agencia,
   ':CC' => $CC,
   ':CPFCNPJ' => $CPFCNPJ,
   ':TipoChavePix' => $TipoChavePix,
   ':ChavePix' => $ChavePix,
   ':Ativo' => $Ativo,
   ':Id' => $Id
]);

if ($stmt->rowCount() === 1) {
    echo json_encode(['status' => 'ok', 'mensagem' => 'Registro alterado com sucesso.']);
} else {
    http_response_code(500); 
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao alterar registro.']);
}
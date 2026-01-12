<?php
header("Access-Control-Allow-Origin: *"); // ou coloque o domínio exato, ex: http://localhost
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');
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
$checkSql = "SELECT COUNT(*) FROM ClienteCC WHERE IdBanco = :IdBanco and Agencia = :Agencia and CC = :CC";
$stmtCheck = $pdo->prepare($checkSql);
$stmtCheck->execute([':IdBanco' => $IdBanco, ':Agencia' => $Agencia, ':CC' => $CC]);
$JaExiste = $stmtCheck->fetchColumn();

if ($JaExiste > 0) {
    http_response_code(403); 
    echo json_encode(['status' => 'erro', 'mensagem' => 'Banco, Agência e Conta Corrente já cadastrado.']);
    exit;
}

$sql = "INSERT INTO ClienteCC (
    IdCliente,
    NomeCC,
    IdBanco,
    Agencia,
    CC,
    CPFCNPJ,
    TipoChavePix,
    ChavePix,
    Ativo
) VALUES (
   :IdCliente,
   :NomeCC,
   :IdBanco,
   :Agencia,
   :CC,
   :CPFCNPJ,
   :TipoChavePix,
   :ChavePix,
   :Ativo
)";

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
   ':Ativo' => $Ativo
]);

if ($stmt->rowCount() === 1) {
    $userId = $pdo->lastInsertId();
    //echo json_encode(['status' => 'ok', 'userId' => $userId, 'nome' => $nome, 'username' => $userName, 'email' => $email]);
    echo json_encode(['status' => 'ok', 'mensagem' => 'Registro incluido com sucesso.']);
} else {
    http_response_code(500); 
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao inserir registro.']);
}
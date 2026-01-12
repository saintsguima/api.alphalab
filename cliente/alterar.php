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

// Função para limpar pontuação
function onyNumber(string $valor): string
{
    return preg_replace('/\D/', '', $valor);
}

require '../DbConnection/conexao.php';

$data = json_decode(file_get_contents('php://input'), true);

$clienteId     = $data['clienteid'] ?? '';
$nome          = $data['nome'] ?? '';
$cpf           = isset($data['cpf']) ? onyNumber($data['cpf']) : '';
$telefone      = $data['telefone'] ?? '';
$email         = $data['email'] ?? '';
$ativo         = $data['ativo'] ?? '1'; // true/false para 1/0
$enviowhatsapp = $data['enviowhatsapp'] ?? '1';
$envioemail    = $data['envioemail'] ?? '1';

// Verificar se o e-mail já está cadastrado
$checkEmailSql = "SELECT COUNT(*) FROM cliente WHERE Email = :email AND Id != :clienteId";
$stmtCheck     = $pdo->prepare($checkEmailSql);
$stmtCheck->execute([':email' => $email, 'clienteId' => $clienteId]);
$emailJaExiste = $stmtCheck->fetchColumn();

if ($emailJaExiste > 0) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'E-mail já cadastrado.']);
    exit;
}

$sql = "UPDATE cliente SET Nome = :nome, CPF = :cpf, Telefone = :telefone, Email = :email, Ativo = :ativo, EnvioWhatsapp = :EnvioWhatsapp, EnvioEmail = :EnvioEmail";
$sql .= " WHERE Id = :clienteId";

$stmt = $pdo->prepare($sql);
try {
    $stmt->execute([
        ':clienteId'     => $clienteId,
        ':nome'          => $nome,
        ':cpf'           => $cpf,
        ':telefone'      => $telefone,
        ':email'         => $email,
        ':ativo'         => $ativo,
        ':EnvioWhatsapp' => $enviowhatsapp,
        ':EnvioEmail'    => $envioemail,

    ]);

    $linhasAfetadas = $stmt->rowCount();
    if ($linhasAfetadas >= 0) {
        $mensagem = ($linhasAfetadas > 0) ? 'Registro alterado com sucesso.' : 'Registro encontrado, mas nenhum dado foi alterado.';

        http_response_code(200);
        echo json_encode(['status' => 'ok', 'mensagem' => $mensagem]);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'erro', 'mensagem' => 'Erro interno ao tentar alterar o registro.']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'erro', 'mensagem' => 'Falha no banco de dados: ' . $e->getMessage()]);
}

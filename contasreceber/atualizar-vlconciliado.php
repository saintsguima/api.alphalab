<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=utf-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require '../DbConnection/conexao.php';

$data = json_decode(file_get_contents('php://input'), true);

$Id                 = $data["Id"] ?? '';
$UserId             = $data["UserId"] ?? '';
$VlConciliado       = $data["VlConciliado"] ?? '';
$VlAnterior         = $data["VlAnterior"] ?? '';
$ObservacaoAjuste   = $data["ObservacaoAjuste"] ?? '';
$Senha              = $data["Senha"] ?? '';
$senhaCriptografada = hash('sha256', $Senha);

try {

    if (! VerificaUsuario($UserId, $senhaCriptografada, $pdo)) {
        http_response_code(401);
        echo json_encode(['status' => 'erro', 'mensagem' => 'Usuário ou senha inválidos.']);
        exit;
    }

    $sql = "UPDATE ContasReceber set VlConciliado = :VlConciliado  WHERE Id = :Id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':Id'           => $Id,
        ':VlConciliado' => $VlConciliado,
    ]);

    $sql2 = "INSERT INTO CrAjusteConciliado (
        ContasReceberId,
        VlAnterior,
        VlAtual,
        usuarioId,
        Observacao) VALUES(
        :Id,
        :VlAnterior,
        :VlAtual,
        :UserId,
        :ObservacaoAjuste)";

    $stmt2 = $pdo->prepare($sql2);
    $stmt2->execute([
        ':Id'               => $Id,
        ':VlAnterior'       => $VlAnterior,
        ':VlAtual'          => $VlConciliado,
        ':UserId'           => $UserId,
        ':ObservacaoAjuste' => $ObservacaoAjuste,
    ]);
    echo json_encode(['status' => 'ok', 'mensagem' => 'Registro alterado com sucesso.']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao alterar o registro.<br/>' . $e->getMessage()]);
}

function VerificaUsuario($UserId, $Senha, $pdo)
{
    $sql  = "SELECT id FROM usuario WHERE Id = :UserId AND pwd = :Senha AND Ativo = 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':UserId' => $UserId, ':Senha' => $Senha]);
    return $stmt->rowCount() === 1;
}

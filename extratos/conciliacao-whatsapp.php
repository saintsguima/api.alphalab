<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

require '../DbConnection/conexao.php'; // Assume que $pdo está disponível

// Trata pré-via (preflight) OPTIONS:
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Max-Age: 86400');
    http_response_code(200);
    exit();
}

// Decodifica o JSON do corpo da requisição. A variável $data agora é um array de objetos.
$data = json_decode(file_get_contents('php://input'), true);

$valor       = $data["valor"] ?? '';
$theData     = $data['theData'] ?? '';
$idTransacao = $data['idTransacao'] ?? '';
$telefone    = removerDDI($data['telefone'], '55') ?? '';

$pdo->beginTransaction();
try {
    $result = false;

    $result = verificaIdTransacaoByIdCliente($pdo, $idTransacao);
    if (! $result) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode([
            'status'   => 'erro',
            'mensagem' => 'Erro o PIX apresentado já foi processado.',
        ]);
        exit();
    }

    $result = insertExtrato($pdo, $valor, $theData, $idTransacao, $telefone);
    if (! $result) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode([
            'status'   => 'erro',
            'mensagem' => 'Erro ao inserir dados no Extrato',
        ]);
        exit();
    }

    $idCliente = GetIdClienteByTelefone($pdo, $telefone);
    if ($idCliente == 0) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode([
            'status'   => 'erro',
            'mensagem' => 'Erro - Cliente não encontrado.',
        ]);
        exit();
    }

    $result = UpdateContasReceber($pdo, $idCliente, $valor, $theData);
    if (! $result) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode([
            'status'   => 'erro',
            'mensagem' => 'Erro ao atualizar o Contas a Receber',
        ]);
        exit();
    }

    $result = insertExtratoCliente($pdo, $idCliente, $valor, $theData);
    if (! $result) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode([
            'status'   => 'erro',
            'mensagem' => 'Erro ao Inserir o Extrato do Cliente',
        ]);
        exit();
    }

    $pdo->commit();
    http_response_code(200);
    echo json_encode([
        'status'   => 'ok',
        'mensagem' => "Conciliação WHATSAPP executada com sucesso.",
        'erros'    => 0,
    ]);
} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode([
        'status'   => 'erro',
        'mensagem' => 'Não foi possível realizar a conciliação: ' . $e->getMessage(),
    ]);
}

function verificaIdTransacaoByIdCliente($pdo, $idTransacao)
{
    $sql = "SELECT COUNT(*) FROM Extratos WHERE Nome = :idTransacao ";

    $stmt = $pdo->prepare($sql);

    $params = [
        ':idTransacao' => $idTransacao,
    ];

    $stmt->execute($params);

    $count = $stmt->fetchColumn();

    return ($count == 0);
}

function GetIdClienteByTelefone($pdo, $telefone)
{
    $sql = "
        SELECT
            Id
        FROM
            cliente
        WHERE
            Telefone = :Telefone
            AND Ativo = 1
        LIMIT 1
    ";
    $stmt = $pdo->prepare($sql);

    $params = [
        ':Telefone' => $telefone,
    ];

    $stmt->execute($params);

    $id = $stmt->fetchColumn();

    if ($id !== false) {
        return (int) $id;
    } else {
        return 0;
    }

}

function insertExtratoCliente($pdo, $idCliente, $valor, $theData)
{

    try {
        $sql = "
            INSERT INTO ExtratoCliente (
                IdCliente,
                Data,
                Historico,
                Credito,
                Debito,
                Saldo
            ) VALUES(
                :IdCliente,
                DATE(:Data),
                :Historico,
                :Credito,
                :Debito,
                :NovoSaldo
            )
        ";

        $SaldoAnterior = getLastSaldo($pdo, $idCliente);
        $NovoSaldo     = $SaldoAnterior + $valor;

        $stmt = $pdo->prepare($sql);

        $params = [
            ':IdCliente' => $idCliente,
            ':Data'      => $theData,
            ':Historico' => 'Conciliação WHATSAPP',
            ':Credito'   => $valor,
            ':Debito'    => 0.0,
            ':NovoSaldo' => $NovoSaldo,
        ];

        $stmt->execute($params);

        return true;
    } catch (PDOException $e) {
        return false;
    }
}

function getLastSaldo($pdo, $IdCliente)
{
    $sql  = "SELECT Saldo FROM ExtratoCliente WHERE IdCliente = :IdCliente ORDER BY Id DESC LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':IdCliente' => $IdCliente]);
    $Saldo = $stmt->fetchColumn();
    return ($Saldo !== false) ? (float) $Saldo : 0.0;
}

function UpdateContasReceber($pdo, $idCliente, $valor, $theData)
{
    try {
        $sql = "
            UPDATE
                ContasReceber
            SET
                VlConciliado = VlConciliado + :valor
            WHERE
                DtInicio <= DATE(:theData)
                AND DtFinal >= DATE(:theData)
                AND IdCliente = :idCliente
        ";

        $stmt = $pdo->prepare($sql);

        $params = [
            ':valor'     => $valor,
            ':theData'   => $theData, // Passamos a data/hora completa. O MySQL se encarrega de extrair apenas a data.
            ':idCliente' => $idCliente,
        ];

        $stmt->execute($params);

        return true;
    } catch (PDOException $e) {
        return false;
    }

}

function insertExtrato($pdo, $valor, $theData, $idTransacao, $telefone)
{
    try {
        $sql = "
            INSERT INTO Extratos (
                IdTipoBanco,
                IdArquivoExtrato,
                Data,
                Linha,
                Lancamento,
                Nome,
                CPFCNPJ,
                Valor,
                Conciliado
            ) VALUES(
                :IdTipoBanco,
                :IdArquivoExtrato,
                CURDATE(),
                :Linha,
                :Lancamento,
                :Nome,
                :CPFCNPJ,
                :Valor,
                :Conciliado
            )
        ";
        $stmt = $pdo->prepare($sql);

        $params = [
            ':IdTipoBanco'      => 4,
            ':IdArquivoExtrato' => 0,
            ':Linha'            => 0,
            ':Lancamento'       => 'WHATSAPP',
            ':Nome'             => $idTransacao,
            ':CPFCNPJ'          => $telefone,
            ':Valor'            => $valor,
            ':Conciliado'       => 1,
        ];

        $stmt->execute($params);

        return true;
    } catch (PDOException $e) {
        return false;
    }

}

function removerDDI(string $telefone, string $ddiParaRemover = '55'): string
{
    // 1. Converte o telefone para string e remove espaços/caracteres não-dígitos para garantir limpeza
    // (Útil se o telefone vier com máscara, embora o exemplo não tenha).
    $telefoneLimpo = preg_replace('/\D/', '', $telefone);

    // 2. Verifica se a string limpa começa com o DDI esperado.
    // str_starts_with é a função mais moderna e eficiente para isso.
    if (str_starts_with($telefoneLimpo, $ddiParaRemover)) {
        // 3. Remove o DDI do início da string.
        // substr() remove os primeiros N caracteres (onde N é o comprimento do DDI).
        return substr($telefoneLimpo, strlen($ddiParaRemover));
    }

    // 4. Se a string não começar com o DDI, retorna o telefone original
    // (assumindo que ele já está no formato correto ou o DDI não estava lá).
    return $telefoneLimpo;
}
// $sql = "insert into teste (valor, telefone, theData) values (:valor, :telefone, :theData)";

// $stmt = $pdo->prepare($sql);

// $params = [
//     ':valor'    => $valor,
//     ':theData'  => $theData,
//     ':telefone' => $telefone,
// ];

// $stmt->execute($params);

// echo json_encode(['status' => 'ok', 'valor:' => $valor, 'data:' => $theData]);

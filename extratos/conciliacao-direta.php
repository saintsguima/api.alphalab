<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

// Trata pré-via (preflight) OPTIONS:
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Max-Age: 86400');
    http_response_code(200);
    exit();
}

// --- Resto do seu código POST ---
require '../DbConnection/conexao.php';

// Decodifica o JSON do corpo da requisição. A variável $data agora é um array de objetos.
$data = json_decode(file_get_contents('php://input'), true);

// Verifica se o JSON foi decodificado corretamente e se é um array
if (json_last_error() !== JSON_ERROR_NONE || ! is_array($data) || empty($data)) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'erro', 'mensagem' => 'JSON inválido ou vazio.']);
    exit();
}

$successCount = 0;
$errorCount   = 0;

// Inicia uma transação para garantir que todas as inserções sejam feitas de uma vez
$pdo->beginTransaction();

try {

    foreach ($data as $item) {
        $formatoOriginal = 'd/m/Y';
        $dt              = DateTime::createFromFormat($formatoOriginal, $item['data']);

        $primeiroDiaObjeto = clone $dt;
        $ultimoDiaObjeto   = clone $dt;

        // Obtém o primeiro dia do mês e formata como string
        $primeiroDia = $primeiroDiaObjeto->modify('first day of this month')->format('Y-m-d');

        // Obtém o último dia do mês e formata como string
        $ultimoDia = $ultimoDiaObjeto->modify('last day of this month')->format('Y-m-d');

        $retorno = UpsertContasReceber($pdo, $item, $primeiroDia, $ultimoDia);
        if (! $retorno) {
            $pdo->rollBack();
            http_response_code(500);
            echo json_encode([
                'status'   => 'erro',
                'mensagem' => 'Erro ao inserir dados de Contas a Receber',
            ]);
            exit();
        }

        $retorno = insertExtratoCliente($pdo, $item);
        if (! $retorno) {
            $pdo->rollBack();
            http_response_code(500);
            echo json_encode([
                'status'   => 'erro',
                'mensagem' => 'Erro ao inserir dados de Extrato do Cliente',
            ]);
            exit();
        }

        $retorno = checkExtrato($pdo, ltrim($item['id'], 'p'));
        if (! $retorno) {
            $pdo->rollBack();
            http_response_code(500);
            echo json_encode([
                'status'   => 'erro',
                'mensagem' => 'Erro ao fazer o checkout no extrato',
            ]);
            exit();
        }

    }

    $pdo->commit();

    http_response_code(200);
    echo json_encode([
        'status'   => 'ok',
        'mensagem' => "Conciliação Direta executada com sucesso.",
        'erros'    => 0,
    ]);

} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode([
        'status'   => 'erro',
        'mensagem' => 'Erro ao inserir dados no banco: ' . $e->getMessage(),
    ]);
}

function UpsertContasReceber($pdo, $item, $primeiroDia, $ultimoDia)
{
    try {
        $sql = "
    INSERT INTO ContasReceber  (
        IdCliente,
        DtInicio,
        DtFinal,
        VlTotal,
        VlConciliado,
        Dtcc,
        IdUsuarioInclusao,
        Ativo
    ) VALUES (
        :IdCliente,
        :DtInicio,
        :DtFinal,
        :VlTotal,
        :VlConciliado,
        :Dtcc,
        :IdUsuarioInclusao,
        :Ativo
    )
    ON DUPLICATE KEY UPDATE
        VlTotal = VlTotal + VALUES(VlTotal),
        VlConciliado = VlConciliado + VALUES(VlConciliado),
        Dtcc = VALUES(Dtcc),
        IdUsuarioInclusao = VALUES(IdUsuarioInclusao),
        Ativo = VALUES(Ativo);
    ";
        $stmt = $pdo->prepare($sql);

        $valorDigitado = 0.0;
        $valorOriginal = 0.0;

        if ($item['valorDigitado'] == "") {
            $valorDigitado = (float) $item['valorOriginal'];
        } else {
            $valorDigitado = (float) $item['valorDigitado'];
        }

        $valorOriginal = (float) $item['valorOriginal'];

        $params = [
            ':IdCliente'         => $item['depto'] ?? null,
            ':DtInicio'          => $primeiroDia ?? null,
            ':DtFinal'           => $ultimoDia ?? null,
            ':VlTotal'           => $valorOriginal,
            ':VlConciliado'      => $valorDigitado,
            ':Dtcc'              => date('Y-m-d H:i:s'),
            ':IdUsuarioInclusao' => 1,
            ':Ativo'             => 1,
        ];

        $stmt->execute($params);

        return true;

    } catch (PDOException $e) {
        // Opcional: logar o erro para depuração
        // error_log("Erro ao executar Upsert: " . $e->getMessage());
        return false;
    }

}

function insertExtratoCliente($pdo, $item)
{
    try {
        //cliente, $Data, $Valor
        $cliente         = $item['depto'] ?? null;
        $formatoOriginal = 'd/m/Y';
        $dt              = DateTime::createFromFormat($formatoOriginal, $item['data']);
        $qdata           = clone $dt;
        $Valor           = 0.0;

        if ($item['valorDigitado'] == "") {
            $Valor = (float) $item['valorOriginal'];
        } else {
            $Valor = (float) $item['valorDigitado'];
        }

        // Obtém o primeiro dia do mês e formata como string
        $Data = $qdata->modify('first day of this month')->format('Y-m-d');

        $SaldoAnterior = getLastSaldo($pdo, $cliente);
        $NovoSaldo     = $SaldoAnterior + $Valor;

        $sql = "INSERT INTO ExtratoCliente(IdCliente, Data, Historico, Credito, Debito, Saldo)
            VALUES (:IdCliente, :Data, :Historico, :Credito, :Debito, :NovoSaldo)";

        $stmt   = $pdo->prepare($sql);
        $params = [
            ':IdCliente' => $cliente,
            ':Data'      => $Data,
            ':Historico' => 'Conciliação Direta',
            ':Credito'   => $Valor,
            ':Debito'    => 0.0,
            ':NovoSaldo' => $NovoSaldo,
        ];

        $stmt->execute($params);

        return true;
    } catch (PDOException $e) {
        return false;
    }
}

function getLastSaldo($pdo, $cliente)
{
    $sql  = "SELECT Saldo FROM ExtratoCliente WHERE IdCliente = :IdCliente ORDER BY Id DESC LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':IdCliente' => $cliente]);
    $Saldo = $stmt->fetchColumn();
    return ($Saldo !== false) ? (float) $Saldo : 0.0;
}

function checkExtrato($pdo, $Id)
{
    $sql  = "UPDATE Extratos SET Conciliado = 1 WHERE Id = :Id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':Id' => $Id]);
    return true;
}

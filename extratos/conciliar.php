<?php
// Configurações iniciais
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

require '../DbConnection/conexao.php'; // Assume que $pdo está disponível

// ==========================================================
// 1. CLASSE PRINCIPAL PARA CONCILIAÇÃO
// ==========================================================
class Conciliador {
    private PDO $pdo;

    // Injeta a conexão PDO no construtor
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // Método principal que orquestra a conciliação
    public function processarExtrato() : array
    {
        // Tratamento de requisição OPTIONS (Preflight)
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit(); 
        }

        $sql = "SELECT 
                    Id, IdTipoBanco, IdArquivoExtrato, Data, Linha, Lancamento, Nome, CPFCNPJ, Valor, Conciliado 
                FROM Extratos 
                WHERE Conciliado = 0";

        $stmt = $this->pdo->prepare($sql);
        // Não há parâmetros para o WHERE Conciliado = 0, então execute sem parâmetros
        $stmt->execute(); 

        // Inicia a transação (garante que tudo seja desfeito em caso de erro)
        $this->pdo->beginTransaction();
        
        try {
            while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) 
            {
                $cliente = $this->acharCliente($linha['CPFCNPJ']);

                if ($cliente == 0){
                    $cliente = $this->acharClienteCC($linha['CPFCNPJ']);
                }
                
                if ($cliente != 0){
                    $contaReceber = $this->acharContaReceber($cliente, $linha['Data']);

                    if ($contaReceber != 0){
                        // Usa o floatval() para garantir que é um float
                        $valorFloat = floatval($linha['Valor']); 
                        
                        $updateCR = $this->updateContaReceber($contaReceber, $valorFloat);
                        
                        if ($updateCR > 0){ // Verifica se a atualização afetou 1 ou mais linhas
                            $insertExtatoCliente = $this->insertExtratoCliente($cliente, $linha['Data'], $valorFloat);
                            
                            if ($insertExtatoCliente > 0) { // Verifica se o insert foi bem sucedido
                                $check = $this->checkExtrato($linha['Id']);
                            }
                        }
                    }
                } 
            }

            $this->pdo->commit();

            return ['status' => 'ok', 'mensagem' => 'Registros Conciliados com sucesso.'];
        }
        catch(\Throwable $e) // CORREÇÃO: \Throwable (com 'a')
        {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            } 
            // Log do erro real (apenas para debug)
            // error_log("Erro na conciliação: " . $e->getMessage()); 

            return ['status' => 'erro', 'mensagem' => 'Falha na conciliação: ' . $e->getMessage()];
        }
    }

    // --- FUNÇÕES DE LÓGICA (MÉTODO DA CLASSE) ---

    private function insertExtratoCliente(int $cliente, string $Data, float $Valor) : int
    {
        $SaldoAnterior = $this->getLastSaldo($cliente);
        $NovoSaldo = $SaldoAnterior + $Valor;

        $sql = "INSERT INTO ExtratoCliente(IdCliente, Data, Historico, Credito, Debito, Saldo) 
                VALUES (:IdCliente, :Data, :Historico, :Credito, :Debito, :NovoSaldo)";

        $stmt = $this->pdo->prepare($sql);
        $params = [
            ':IdCliente' => $cliente,
            ':Data'      => $Data,
            ':Historico' => 'Conciliado',
            ':Credito'   => $Valor,
            ':Debito'    => 0.0,
            ':NovoSaldo' => $NovoSaldo
        ];

        $stmt->execute($params); 
        return (int) $this->pdo->lastInsertId();
    }
    
    private function acharCliente(string $CPFCNPJ) : int {
        $sql = "SELECT id FROM cliente WHERE CPF = :CPF";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':CPF' => $CPFCNPJ]);
        $id_cliente = $stmt->fetchColumn();
        return ($id_cliente !== false) ? (int) $id_cliente : 0;
    }

    private function getLastSaldo(int $cliente) : float {
        $sql = "SELECT Saldo FROM ExtratoCliente WHERE IdCliente = :IdCliente ORDER BY Id DESC LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':IdCliente' => $cliente]);
        $Saldo = $stmt->fetchColumn(); 
        return ($Saldo !== false) ? (float) $Saldo : 0.0; 
    }

    private function acharClienteCC(string $CPFCNPJ) : int {
        $sql = "SELECT IdCliente FROM ClienteCC WHERE CPFCNPJ = :CPF";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':CPF' => $CPFCNPJ]); 
        $id_cliente = $stmt->fetchColumn(); 
        return ($id_cliente !== false) ? (int) $id_cliente : 0; 
    } 

    private function acharContaReceber(int $cliente, string $Data) : int {
        $sql = "SELECT id FROM ContasReceber WHERE DtInicio <= :Data AND DtFinal >= :Data AND IdCliente = :Id";
        $stmt = $this->pdo->prepare($sql);
        $params = [':Data' => $Data, ':Id' => $cliente];
        $stmt->execute($params); 
        $id = $stmt->fetchColumn(); 
        return ($id !== false) ? (int) $id : 0; 
    } 

    private function updateContaReceber(int $Id, float $Valor) : int {
        $sql = "UPDATE ContasReceber SET VlConciliado = VlConciliado + :Valor WHERE Id = :Id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':Id' => $Id, ':Valor' => $Valor]);
        return $stmt->rowCount();
    }

    private function checkExtrato(int $Id) : int {
        $sql = "UPDATE Extratos SET Conciliado = 1 WHERE Id = :Id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':Id' => $Id]);
        return $stmt->rowCount();
    }
}

// ==========================================================
// 2. INSTANCIAÇÃO E EXECUÇÃO
// ==========================================================
try {
    // 1. Cria a instância da classe, passando a conexão $pdo (do arquivo conexao.php)
    $conciliador = new Conciliador($pdo);

    // 2. Chama o método principal
    $result = $conciliador->processarExtrato();
    
    // 3. Responde à requisição
    http_response_code($result['status'] === 'ok' ? 200 : 500); 
    echo json_encode($result);
} catch(\Throwable $e){
    // Captura exceções antes mesmo da classe ser instanciada (ex: erro de conexão)
    http_response_code(500); 
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro fatal na execução: ' . $e->getMessage()]);
}
?>
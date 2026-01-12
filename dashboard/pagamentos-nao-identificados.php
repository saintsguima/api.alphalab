<?php
// Define as permissões de CORS
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

// --- TRATAMENTO PREFLIGHT (CRÍTICO PARA REQUISIÇÕES REAIS) ---
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(); 
}
// -------------------------------------------------------------

require '../DbConnection/conexao.php'; // Assume que $pdo está disponível

try {
    $resultados = [];

    // ==========================================================
    // 1. PAGAMENTOS NÃO IDENTIFICADOS
    // ==========================================================
    $sql1 = "SELECT SUM(Valor) AS total FROM Extratos WHERE Conciliado != 1";
    $stmt1 = $pdo->prepare($sql1);
    $stmt1->execute();
    $res1 = $stmt1->fetch(PDO::FETCH_ASSOC);
    
    $resultados['TotalNaoConciliado'] = (float) ($res1['total'] ?? 0.0);
    
    // ==========================================================
    // 2. INADIMPLENCIA
    // ==========================================================
    $sql2 = "SELECT SUM(VlTotal - VlConciliado) AS Inadimplencia FROM  ContasReceber"; 
    $stmt2 = $pdo->prepare($sql2);
    $stmt2->execute();
    $res2 = $stmt2->fetch(PDO::FETCH_ASSOC);

    $resultados['TotalAbertos'] = (float) ($res2['Inadimplencia'] ?? 0.0);

    // ==========================================================
    // 3. TOTAL FATURADO NO MES
    // ==========================================================
    $sql3 = "SELECT  SUM(credito) AS total_credito_mes_atual FROM  ExtratoCliente WHERE  YEAR(data) = YEAR(CURDATE()) AND MONTH(data) = MONTH(CURDATE());"; 
    $stmt3 = $pdo->prepare($sql3);
    $stmt3->execute();
    $res3 = $stmt3->fetch(PDO::FETCH_ASSOC);

    $resultados['FaturadoNoMes'] = (float) ($res3['total_credito_mes_atual'] ?? 0.0);

     // ==========================================================
    // 4. TOTAL A RECEBER NO MES    
    // ==========================================================
    $sql4 = "SELECT SUM(VlTotal) AS AReceber  FROM ContasReceber WHERE DtInicio <= CURDATE() AND DtFinal >= CURDATE();"; 
    $stmt4 = $pdo->prepare($sql4);
    $stmt4->execute();
    $res4 = $stmt4->fetch(PDO::FETCH_ASSOC);

    $resultados['TotalAReceber'] = (float) ($res4['AReceber'] ?? 0.0);

    // ==========================================================
    // 3. RETORNO FINAL
    // ==========================================================
    
    // Prepara a resposta de sucesso, incluindo todos os dados
    $response = [
        'status' => 'ok',
        'dados' => $resultados // Todos os totais estão dentro da chave 'dados'
    ];
    
    http_response_code(200);
    echo json_encode($response);

} catch (PDOException $e) {
    // Tratamento de erro do banco de dados
    http_response_code(500);
    echo json_encode([
        'status' => 'erro',
        'mensagem' => 'Erro no banco de dados: ' . $e->getMessage()
    ]);
}
?>
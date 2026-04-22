<?php
// Define as permissões de CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Opcional: defina tempo de cache do preflight
    header('Access-Control-Max-Age: 86400');
    http_response_code(200);
    exit();
}

require '../DbConnection/conexao.php'; // Assume que $pdo está disponível

$data = json_decode(file_get_contents('php://input'), true);

$Ano = $data['Ano'] ?? '';
$Mes = $data['Mes'] ?? '';

try {
    $resultados = [];

    // ==========================================================
    // 4. TOTAL A RECEBER NO MES
    // ==========================================================
    // ==========================================================
    // 4. TOTAL A RECEBER NO MES
    // ==========================================================
    $sql4  = "SELECT SUM(VlTotal) AS vlTotal FROM  ContasReceber where month(DtInicio) = :Mes and year(DtInicio) = :Ano";
    $stmt4 = $pdo->prepare($sql4);

    $stmt4->execute([
        ':Mes' => $Mes,
        ':Ano' => $Ano,
    ]);

    $res4 = $stmt4->fetch(PDO::FETCH_ASSOC);

    $resultados['TotalAReceberNoMes'] = (float) ($res4['vlTotal'] ?? 0.0);

    // ==========================================================
    // 3. RETORNO FINAL
    // ==========================================================

    // Prepara a resposta de sucesso, incluindo todos os dados
    $response = [
        'status' => 'ok',
        'dados'  => $resultados, // Todos os totais estão dentro da chave 'dados'
    ];

    http_response_code(200);
    echo json_encode($response);

} catch (PDOException $e) {
    // Tratamento de erro do banco de dados
    http_response_code(500);
    echo json_encode([
        'status'   => 'erro',
        'mensagem' => 'Erro no banco de dados: ' . $e->getMessage(),
    ]);
}

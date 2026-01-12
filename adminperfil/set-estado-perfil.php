<?php
// Define as permissões de CORS
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, DELETE"); 
header("Access-Control-Allow-Headers: Content-Type, Authorization"); 
header('Content-Type: application/json');

// --- LIDAR COM REQUISIÇÃO PREFLIGHT (OPTIONS) ---
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
// ------------------------------------------------

// O RESTANTE DO SEU CÓDIGO COMEÇA AQUI
require '../DbConnection/conexao.php'; // Certifique-se de que a conexão PDO está em $pdo

// 1. Decodificar o Payload
$data = json_decode(file_get_contents('php://input'), true);

// 2. Extrair e validar dados com segurança (usando o null-coalescing ?? [])
$perfil    = $data['perfil'] ?? null; 
$checked   = $data['checked'] ?? null; 
// CORREÇÃO: Garante que $permissoes é um array, mesmo que não venha no payload
$permissoes = $data['permissoes'] ?? []; 

if ($perfil == 1){
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'erro', 'mensagem' => 'Seus dados não foram gravados, pois Admin. não pode ser modificado.']);
    exit();
}
// 3. Validação Inicial (Boa Prática)
if (empty($perfil) || $checked === null || !is_array($permissoes)) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'erro', 'mensagem' => 'Dados de entrada incompletos ou inválidos.']);
    exit();
}

// 4. Execução da Transação
$pdo->beginTransaction();
try {
    // CORREÇÃO: Move a definição da query $sql para fora do loop (melhor performance)
    $sql = "UPDATE PerfilPermissao SET flAtivo = :flAtivo 
            WHERE PerfilId = :PerfilId AND Permissao = :Permissao;";
    
    // Prepara a query uma única vez fora do loop (melhor performance)
    $stmt = $pdo->prepare($sql);
    
    foreach($permissoes as $permissao){
        
        // CORREÇÃO: Correção de sintaxe no array de parâmetros (vírgulas)
        $params = [
            ':flAtivo'  => $checked,
            ':PerfilId' => $perfil,
            ':Permissao'=> $permissao
        ];

        // Executa a query para cada permissão
        $stmt->execute($params);
    }
    
    $pdo->commit();
    http_response_code(200); 
    echo json_encode(['status' => 'ok', 'mensagem' => 'Operação realizada com sucesso.']);
} catch(PDOException $e){
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao executar a operação: ' . $e->getMessage()]);
}

?>
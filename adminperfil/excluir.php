<?php
// Define as permissões de CORS
header("Access-Control-Allow-Origin: *"); 
// Importante: Adicione o método DELETE (boa prática, mesmo que você use POST para a exclusão)
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, DELETE"); 
header("Access-Control-Allow-Headers: Content-Type, Authorization"); 
header('Content-Type: application/json');

// --- NOVO BLOCO PARA LIDAR COM A REQUISIÇÃO PREFLIGHT (OPTIONS) ---
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Para preflight, apenas enviamos os headers e respondemos com 200 OK (padrão)
    http_response_code(200);
    exit(); // Encerra o script antes de tentar conectar ao banco ou processar dados
}
// ------------------------------------------------------------------

// O RESTANTE DO SEU CÓDIGO COMEÇA AQUI
require '../DbConnection/conexao.php';

$data = json_decode(file_get_contents('php://input'), true);

$Id = $data['Id'] ?? null; 

if ($Id == 1){
    http_response_code(500); 
    echo json_encode(['status' => 'Erro', 'mensagem' => 'Admin não pode ser Excluido.']);
    exit();
}

if ($Id === null) {
    http_response_code(400); 
    echo json_encode(['status' => 'erro', 'mensagem' => 'ID não fornecido.']);
    exit;
}

$sql = "DELETE FROM Perfil WHERE Id = :Id";

$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':Id' => $Id]);

    $linhasAfetadas = $stmt->rowCount();

    $sqlDelete = "DELETE FROM PerfilPermissao where PerfilId = :PerfilId;";
    $stmtd = $pdo->prepare($sqlDelete);
    $stmtd->execute([':PerfilId' => $Id]);

    $pdo->commit();
    if ($linhasAfetadas === 1) {
        http_response_code(200); 
        echo json_encode(['status' => 'ok', 'mensagem' => 'Registro excluído com sucesso.']);
    } elseif ($linhasAfetadas === 0) {
        http_response_code(404); 
        echo json_encode(['status' => 'erro', 'mensagem' => 'Registro não encontrado ou já excluído.']);
    } else {
        http_response_code(500); 
        echo json_encode(['status' => 'erro', 'mensagem' => 'Erro interno ao excluir registro.']);
    }
} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao executar a exclusão: ' . $e->getMessage()]);
}

?>



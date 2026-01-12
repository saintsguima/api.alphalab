<?php
// Define as permissões de CORS
header("Access-Control-Allow-Origin: *"); 
// Atualizado: Inclui o método PUT, que é o padrão para alterações completas (UPDATE)
header("Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS"); 
header("Access-Control-Allow-Headers: Content-Type, Authorization"); 
header('Content-Type: application/json');

// --- 1. TRATAMENTO DA REQUISIÇÃO PREFLIGHT (OPTIONS) ---
// Responde ao navegador sobre os métodos permitidos antes de processar a requisição principal.
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(); 
}

// --- 2. VERIFICAÇÃO DO MÉTODO HTTP ---
// A rotina só deve continuar se o método for PUT (para atualização) ou POST (se preferir manter o padrão original).
if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Método Não Permitido
    echo json_encode(['status' => 'erro', 'mensagem' => 'Método HTTP não permitido. Use PUT ou POST.']);
    exit();
}

// --- 3. CONEXÃO E ENTRADA DE DADOS ---
require '../DbConnection/conexao.php';

$data = json_decode(file_get_contents('php://input'), true);

// Usa filter_var para validar se ID é um inteiro válido.
// Usa ?? '' para garantir que $data['Nome'] exista e seja uma string, caso contrário, será string vazia.
$Id = filter_var($data['Id'] ?? null, FILTER_VALIDATE_INT);
$Nome = trim($data['Nome'] ?? ''); // Remove espaços em branco antes/depois

// --- 4. VALIDAÇÃO DE ENTRADA ---

// Verifica se o ID é inválido (não numérico, menor que 1, ou ausente)
if ($Id === false || $Id === null || $Id < 1) {
    http_response_code(400); // Bad Request (Requisição Mal Formada)
    echo json_encode(['status' => 'erro', 'mensagem' => 'ID inválido ou não fornecido.']);
    exit();
}

// Verifica se o Nome está vazio após a limpeza
if (empty($Nome)) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'erro', 'mensagem' => 'O campo Nome não pode ser vazio.']);
    exit();
}

// --- 5. REGRA DE NEGÓCIO: ADMIN NÃO PODE SER ALTERADO ---
if ($Id === 1) {
    http_response_code(403); // Forbidden (Proibido por Regra de Negócio)
    echo json_encode(['status' => 'erro', 'mensagem' => 'O registro de ID 1 (Administrador) não pode ser alterado.']);
    exit();
}

// --- 6. EXECUÇÃO DA QUERY ---
$sql = "UPDATE Perfil SET Nome = :Nome WHERE Id = :Id";

try {
    $stmt = $pdo->prepare($sql);
    // Bind dos parâmetros para o Prepared Statement
    $stmt->bindValue(':Id', $Id, PDO::PARAM_INT);
    $stmt->bindValue(':Nome', $Nome, PDO::PARAM_STR);

    $stmt->execute();

    $linhasAfetadas = $stmt->rowCount();

    if ($linhasAfetadas === 1) {
        http_response_code(200); // OK
        echo json_encode(['status' => 'ok', 'mensagem' => 'Registro alterado com sucesso.']);
    } elseif ($linhasAfetadas === 0) {
        // Se o registro não for encontrado ou se os dados forem os mesmos (0 linhas afetadas)
        http_response_code(404); // Not Found (Recurso não encontrado)
        echo json_encode(['status' => 'erro', 'mensagem' => 'Registro não encontrado ou nenhum dado foi alterado.']);
    } else {
        // Caso inesperado, como mais de uma linha afetada (o que não deve ocorrer com WHERE Id = :Id)
        http_response_code(500); 
        echo json_encode(['status' => 'erro', 'mensagem' => 'Erro interno: Múltiplos registros afetados.']);
    }

} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error (Erro no servidor/DB)
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao executar a alteração: ' . $e->getMessage()]);
}

?>
<?php
header("Access-Control-Allow-Origin: *");
// Atualize os métodos permitidos para incluir OPTIONS
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

// --- TRATAMENTO PARA REQUISIÇÃO OPTIONS (PREFLIGHT) ---
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(); // Sai do script imediatamente!
}
// --------------------------------------------------------

require '../DbConnection/conexao.php';

$data = json_decode(file_get_contents('php://input'), true);

$Id   = $data['Id'] ?? '';
$Nome = trim($data['Nome'] ?? ''); // Use trim() para remover espaços em branco

// --- VALIDAÇÃO REFORÇADA ---
if (empty($Nome)) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'erro', 'mensagem' => 'O campo Nome é obrigatório.']);
    exit;
}
// ----------------------------

$sql = "INSERT INTO Perfil (
    Nome
) VALUES (
    :Nome
)";

$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':Nome' => $Nome,
    ]);

    if ($stmt->rowCount() === 1) {
        $perfilId = $pdo->lastInsertId();

        $permissoes = ['01', '02', '0201', '0202', '0203', '03', '0301', '0302', '0303', '04', '0401', '0402', '0403', '040301', '040302', '0404', '0405', '05', '0501', '0502', '0503', '0504', '06', '0601', '0602', '0603', '0604', '0605', '07', '0701', '0702'];

        $total = inserirPermissoesEmLote($pdo, $perfilId, $permissoes, 1);

        http_response_code(200);
        echo json_encode(['status' => 'ok', 'mensagem' => 'Registro incluído com sucesso.', 'id_inserido' => $perfilId]);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao inserir registro.']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao executar a inserção: ' . $e->getMessage()]);
}
// O bloco 'catch' é importante para garantir que o front-end receba uma resposta JSON em caso de erro de DB.

function inserirPermissoesEmLote(PDO $pdo, int $perfilId, array $permissoes, int $flAtivo = 1): int
{
    if (empty($permissoes)) {
        return 0;
    }

    // Monte os placeholders: (?,?,?),(?,?,?),...
    $placeholders = implode(',', array_fill(0, count($permissoes), '(?,?,?)'));

    $sql = "INSERT INTO PerfilPermissao (PerfilId, Permissao, flAtivo) VALUES {$placeholders}";

    // Achata os parâmetros na ordem dos placeholders
    $params = [];
    foreach ($permissoes as $perm) {
        $params[] = $perfilId; // PerfilId (param)
        $params[] = $perm;     // Permissao
        $params[] = $flAtivo;  // flAtivo
    }

    try {

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $pdo->commit();
        return $stmt->rowCount(); // linhas inseridas
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}
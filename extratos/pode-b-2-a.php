<?php
header("Access-Control-Allow-Origin: *"); // ou coloque o domínio exato, ex: http://localhost
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');
require '../DbConnection/conexao.php';

$data = json_decode(file_get_contents('php://input'), true);

$theCPF = $data["theCPF"]?? '';

$sql = "
    SELECT count(*) total_registros FROM ClienteCC WHERE CPFCNPJ = :CPFCNPJ;
";


$stmt = $pdo->prepare($sql);

$params = [
    ':CPFCNPJ' => $theCPF
];

$stmt->execute($params);

$resultado = $stmt->fetch(PDO::FETCH_ASSOC);
$contagem = (int)$resultado['total_registros'];

$status = ''; 

if ($contagem > 0) {
    // Se count(*) for > 0, o status é 'nok' (false)
    $status = 'nok'; 
} else {
    // Caso contrário, o status é 'ok' (true)
    $status = 'ok';
}

$resposta = [
    'status' => $status,
    'mensagem' => ($status == 'nok' ? 'Não é possível realizar a operação pois o CPF, ja está cadastrado.' : 'Nenhum registro encontrado.')
];

echo json_encode($resposta);

?>
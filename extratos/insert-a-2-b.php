<?php
header("Access-Control-Allow-Origin: *"); // ou coloque o domínio exato, ex: http://localhost
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');
require '../DbConnection/conexao.php';
$data = json_decode(file_get_contents('php://input'), true);

$theCPF = $data["theCPF"]?? '';

try{

    $sqlDELETE = "
        DELETE FROM ClienteCC where CPFCNPJ = :CPFCNPJ;
    ";
    
    $stmtDel = $pdo->prepare($sqlDELETE);
    
    $params = [
        ':CPFCNPJ' => $theCPF
    ];
    
    $stmtDel->execute($params);

    echo json_encode(['status' => 'ok', 'mensagem' => 'Registros Removido.']);        
} catch(Throwable  $e){
    http_response_code(500); 
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro na execução registro. ' . $e->getMessage()]);
}

?>
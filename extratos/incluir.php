<?php
header("Access-Control-Allow-Origin: *"); // ou coloque o domínio exato, ex: http://localhost
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');
require '../DbConnection/conexao.php';
$data = json_decode(file_get_contents('php://input'), true);

$Id = $data["Id"]?? '';
$Documento = $data['documento'] ?? '';
$Valor = $data['valor'] ?? '';
$DtInicio = $data['dtInicio'] ?? '';
$DtFinal = $data['dtFinal'] ?? '';

try{
    $sqlInsert = "
        INSERT INTO ContasReceber (IdCliente, DtInicio, DtFinal, VlTotal, IdUsuarioInclusao)
        VALUES (:Id, :DtInicio, :DtFinal, :VlTotal, :IdUsuarioInclusao);
    ";
    $stmtIns = $pdo->prepare($sqlInsert);
    $stmtIns->execute([
        ':VlTotal'           => $Valor,
        ':IdUsuarioInclusao' => 1,
        ':Id'                => $Id,
        ':DtInicio'          => $DtInicio,
        ':DtFinal'           => $DtFinal,
    ]);

    echo json_encode(['status' => 'ok', 'mensagem' => 'Registros Atualizados .']);        
} catch(Throwable  $e){
    http_response_code(500); 
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro na execução registro.']);
}

?>
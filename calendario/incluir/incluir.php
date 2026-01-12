<?php
header("Access-Control-Allow-Origin: *"); // ou coloque o domínio exato, ex: http://localhost
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');
require '../../DbConnection/connAssessor.php'; 
$data = json_decode(file_get_contents('php://input'), true);

$Titulo = $data['titulo'];
$DtInicio = $data['dtinicio'];
$DtFinal = $data['dtfinal'];
$flAllDaty = $data['flallday'];
$idTipoAgenda = $data['idtipoagenda'];


// Se from tipo agenda, verifica se não existe ja algum agendamento par o periodo mensionado
if ($idTipoAgenda == 1){
    $checkAgendamento = "SELECT COUNT(*) FROM Agenda where DtInicio >= :DtInicio and DtFinal <= :DtFinal and IdTipoAgenda = 1 and IdUser = 1";
    $stmtCheck = $pdo->prepare(@$checkAgendamento);
    $param = [
        'DtInicio' => $DtInicio,
        'DtFinal' => $DtFinal,
    ];

    $stmtCheck->execute($param);

    $jaTemAgendamento = $stmtCheck->fetchColumn();

    if ($jaTemAgendamento > 0){
        echo json_encode(['status' => 'erro', 'mensagem' => 'Já Existe um agendamento para essa data.']);    
        exit;
    }
}

if ($flAllDaty == 1){
    $DtFinalDate = new DateTime($DtInicio);
    $DtFinalDate->setTime(23, 59, 59);
    $DtFinal = $DtFinalDate->format('Y-m-d H:i:s');
}

$sql = "INSERT INTO Agenda (IdUser, Titulo, DtInicio, DtFinal, FlAllDaty, IdTipoAgenda, FlAtivo) VALUES (1, :Titulo, :DtInicio, :DtFinal, :flAllDaty, :IdTipoAgenda, 1)";

$stmt = $pdo->prepare($sql);
$param2 = [
    ':Titulo' => $Titulo,
    ':DtInicio' => $DtInicio,
    ':DtFinal' => $DtFinal,
    'flAllDaty' => $flAllDaty,
    'IdTipoAgenda' => $idTipoAgenda
];

$stmt->execute($param2);

if ($stmt->rowCount() === 1) {
    echo json_encode(['status' => 'ok', 'mensagem' => 'Registro incluido com sucesso.']);
} else {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao incluir Categoria.']);
}
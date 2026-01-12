<?php
$host = 'srv952.hstgr.io';
$db = 'u902229595_alphalabs';
$user = 'u902229595_useralphalabs';
$pass = 'al257425227!AG';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro na conexão com o banco de dados']);
    exit;
}
?>
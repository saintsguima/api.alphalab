<?php
// Headers CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Monta o caminho completo para listar.php
$baseUrl = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/listar.php';

// Faz uma requisição interna via GET (sem corpo)
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// NÃO envia POST nem corpo
// curl_setopt($ch, CURLOPT_POST, true); <- REMOVA
// curl_setopt($ch, CURLOPT_POSTFIELDS, $body); <- REMOVA

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Retorna a resposta de listar.php como se fosse da index.php
http_response_code($httpCode);
header('Content-Type: application/json');
echo $response;

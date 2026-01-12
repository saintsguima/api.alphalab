<?php
date_default_timezone_set('America/Sao_Paulo');

function somaDigitosAteUm($numero) {
    // Enquanto o número tiver mais de 1 dígito
    while ($numero > 9) {
        // Separa os dígitos e soma
        $numero = array_sum(str_split($numero));
    }
    return $numero;
}


$password = '123456';
$senhaCriptografada = hash('sha256', $password);

$microtime = microtime(true);
$segundos = date('s', $microtime);
$milissegundos = sprintf("%03d", ($microtime - floor($microtime)) * 1000);

$var0="5feceb66ffc86f38d952786c6d696c79c2dbc239dd4e91b46729d73a27fb57e9";
$var1="6b86b273ff34fce19d6b804eff5a3f5747ada4eaa22f1d49c01e52ddb7875b4b";
$var2="d4735e3a265e16eee03f59718b9b5d03019c07d8b6c41f6c1ecae56d5653f8c6";
$var3="4e07408562bedb8b60ce05c1decfe3ad16b7223095b7a4b9587c7ff0adf7aabd";
$var4="4b227777d4dd1fc61c6f884f48641d02b6c6f5203f4e8c783a4a01be7a8d86bf";
$var5="ef2d127de37b94285d31e98e50a251ec9621a34d6dcdc23c7b660ed5329a52ee";
$var6="e150a1ec81e8e8fdfbd6cbe0068f35421e837ff078757b31b7fc7b6b64337e2c";
$var7="ec373c483331178a20e4cea97ce8ac591e8bda4318c28459d9b48544e1c05e46";
$var8="74e6f7298a9c2d168935f58c001bad88c58434b6d42ce9a8952f8f3e396ba61f";
$var9="19556f25e9c889fa6355703a850e7c6ada7cabc26027c92f8d5f51e86c31e2ef";

// echo $segundos;
// echo "-";
// echo $milissegundos;
// echo "-";
// echo somaDigitosAteUm($milissegundos);
// echo "-";

// if ($segundo % 2 == 0){
//     substr($str, 0, 5);
// }
// echo $senhaCriptografada;


$str = $var9;
$tamanho_bloco = 4;

// === Etapa 1: Preencher a string se necessário ===
$restante = strlen($str) % $tamanho_bloco;
$padding = '';
if ($restante !== 0) {
    $pad_length = $tamanho_bloco - $restante;
    // Usar caractere especial para padding
    $padding = str_repeat('&', $pad_length);
    $str_padded = $str . $padding;
} else {
    $str_padded = $str;
}

// === Etapa 2: Dividir em blocos ===
$blocos = str_split($str_padded, $tamanho_bloco);

// === Etapa 3: Inverter ===
$blocos_invertidos = array_reverse($blocos);

// === Etapa 4: Juntar ===
$str_invertida = implode('', $blocos_invertidos);

echo "Original....: $str\n";
echo "Invertida...: $str_invertida\n";

// === Etapa 5: Para reverter ao original ===
$blocos_reverter = str_split($str_invertida, $tamanho_bloco);
$blocos_original = array_reverse($blocos_reverter);
$str_revertida = implode('', $blocos_original);

// === Etapa 6: Remover o padding, se existir ===
if ($padding) {
    $str_revertida = substr($str_revertida, 0, strlen($str));
}

echo "Recuperada..: $str_revertida\n";

$data = date('Y-m-d H:i:s');

// 2. Defina uma chave secreta (precisa ter o tamanho correto para o algoritmo escolhido!)
$key = 'MinhaChaveSecreta1234567'; // Exemplo de 24 bytes para 'aes-192-cbc'
$method = 'aes-192-cbc';

// 3. IV (Vetor de Inicialização) – deve ter o tamanho correto (16 bytes para AES)
$iv = random_bytes(openssl_cipher_iv_length($method));

// 4. Encripte
$encrypted = openssl_encrypt($data, $method, $key, OPENSSL_RAW_DATA, $iv);

// Se quiser armazenar, pode salvar o IV junto dos dados encrypted:
$encrypted_data = $iv . $encrypted;

// 5. Para decriptar, recupere o IV e os dados
$iv_dec = substr($encrypted_data, 0, openssl_cipher_iv_length($method));
$encrypted_dec = substr($encrypted_data, openssl_cipher_iv_length($method));

// 6. Decripte
$decrypted = openssl_decrypt($encrypted_dec, $method, $key, OPENSSL_RAW_DATA, $iv_dec);

echo "Original:   $data\n";
echo "Encriptado (binário): " . bin2hex($encrypted_data) . "\n"; // Para visualização
echo "Decriptado: $decrypted\n";

date_default_timezone_set('America/Sao_Paulo');
echo date('d/m/Y H:i', strtotime('+2 hours'));

?>

<?php
// Crypto.php

class Crypto
{
    private $cipher = "AES-256-CBC";

    private function gerarChave()
    {
        return substr(hash('sha256', date('YmdHis') . rand()), 0, 32);
    }

    public function crypt($mensagem)
    {
        $chave = $this->gerarChave();
        $iv = openssl_random_pseudo_bytes(16);

        $criptografado = openssl_encrypt($mensagem, $this->cipher, $chave, 0, $iv);

        return base64_encode($chave . $iv . $criptografado);
    }

    public function dcrypt($mensagemCriptografada)
    {
        $dados = base64_decode($mensagemCriptografada);

        $chave = substr($dados, 0, 32);
        $iv = substr($dados, 32, 16);
        $conteudo = substr($dados, 48);

        return openssl_decrypt($conteudo, $this->cipher, $chave, 0, $iv);
    }
}

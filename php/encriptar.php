<?php
$clave  = 'clave_para_realizar_encriptacion'; //Configuración del algoritmo de encriptación
$method = 'aes-256-cbc'; //Metodo de encriptación

$iv = base64_decode("6RxsQB+Ok8jrnX61ac2mzg=="); //Puedes generar una diferente usando la funcion $getIV()
/*
 Encripta el contenido de la variable, enviada como parametro.
  */
$encriptar = function ($valor) use ($method, $clave, $iv) {
    return openssl_encrypt($valor, $method, $clave, false, $iv);
};
/*
 Desencripta el texto recibido
 */
$desencriptar = function ($valor) use ($method, $clave, $iv) {
    return openssl_decrypt($valor, $method, $clave, false, $iv);
};
/*
 Genera un valor para IV
 */
$getIV = function () use ($method) {
    return base64_encode(random_bytes(openssl_cipher_iv_length($method)));
};
?>
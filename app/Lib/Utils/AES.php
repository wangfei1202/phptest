<?php

namespace App\Lib\Utils;

class AES
{
    /**
     * @var string
     */
    const PASS = '3d75z237f9be6485';
    
    const IV = 'ba1c0bb73b62f82d';
    
    public static function encrypt($plaintext, $method = 'aes-128-cbc')
    {
        return base64_encode(openssl_encrypt($plaintext, $method, self::PASS, OPENSSL_RAW_DATA, self::IV));
    }
    
    public static function decrypt($encrypted, $method = 'aes-128-cbc')
    {
        return openssl_decrypt(base64_decode($encrypted), $method, self::PASS, OPENSSL_RAW_DATA, self::IV);
    }
}
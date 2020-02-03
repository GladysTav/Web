<?php
namespace App;
class Chiffrement {
/*
// Algorithme utilisé pour le cryptage des blocs
private static $cipher  = MCRYPT_RIJNDAEL_128;

// Clé de cryptage
private static $key = "YouShallNotPass";

// Mode opératoire (traitement des blocs)
private static $mode    = 'cbc';

public static function encrypt($data){
    $keyHash = md5(self::$key);
    $key = substr($keyHash, 0, mcrypt_get_key_size(self::$cipher, self::$mode));
    $iv  = substr($keyHash, 0, mcrypt_get_block_size(self::$cipher, self::$mode));

    $data = mcrypt_encrypt(self::$cipher, $key, $data, self::$mode, $iv);

    return base64_encode($data);
}

public static function decrypt($data){
    $keyHash = md5(self::$key);
    $key = substr($keyHash, 0,   mcrypt_get_key_size(self::$cipher, self::$mode) );
    $iv  = substr($keyHash, 0, mcrypt_get_block_size(self::$cipher, self::$mode) );

    $data = base64_decode($data);
    $data = mcrypt_decrypt(self::$cipher, $key, $data, self::$mode, $iv);

    return rtrim($data);
}*/

public static function encrypt($clearMdp)
{
  $hash = password_hash($clearMdp,PASSWORD_BCRYPT,['cost' => 13]);
  return $hash;
}



}

?>

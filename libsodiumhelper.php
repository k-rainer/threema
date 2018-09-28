<?php

class LibsodiumHelper {

    public static function generatePadBytes() {
        $padBytes = 0;
        while ($padBytes < 0 || $padBytes > 255) {
            $padBytes = ord(\Sodium\randombytes_buf(1));
        }
        return $padBytes;
    }

    public static function proccessUncencryptedMessage($message) {

        $textBytes = '\x01' . $message;
        $padBytes = self::generatePadBytes();
        $textBytes .= str_repeat(chr($padBytes), $padBytes);

        return $textBytes;

    }

    public static function generateNonce() {
        return \Sodium\randombytes_buf(24);
    }

    public static function generateBoxKeyPair($privateKey, $publicKey) {
        return \Sodium\crypto_box_keypair_from_secretkey_and_publickey($privateKey, $publicKey);
    }

    public static function openBox($boxKeyPair, $nonce, $message) {
        return \Sodium\crypto_box_open($message, $nonce, $boxKeyPair);
    }

    public static function processEncryptedMessage($message) {
        $padBytes = ord($message[strlen($message) - 1]);
        $realDataLength = strlen($message) - $padBytes;

        if($realDataLength < 1) {
            throw new Exception('Bad Message');
        }

        $data = substr($message, 1, $realDataLength - 1);
        $type = ord($message[0]);

        return [
            'type' => $type,
            'data' => $data,
        ];
    }

    public static function hex2bin($data) {
        return \Sodium\hex2bin($data);
    }

    public static function bin2hex($data) {
        return \Sodium\bin2hex($data);
    }

    public static function makeBox($keyPair, $nonce, $data) {
        return \Sodium\crypto_box($data, $nonce, $keyPair);
    }

}
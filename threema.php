<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/curlhelper.php';
require_once __DIR__ . '/libsodiumhelper.php';
require_once __DIR__ . '/threema.php';

class Threema {

    /**
     * @var Configuration $configuration
     */
    protected $configuration = null;

    public function __construct() {
        $this->configuration = new Config();
    }


    /**
     * @param $to
     * @param $from
     * @return mixed
     * @throws \Exception
     */
    public function getPublicKeyForId($to, $from) {

        $secret = $this->configuration->getSecretForId($from);

        $resp = CurlHelper::get('https://msgapi.threema.ch/pubkeys/' . $to, [
            'secret' => $secret,
            'from' => $from
        ]);

        return LibsodiumHelper::hex2bin($resp['data']);

    }

    /**
     * @param $from
     * @param $to
     * @param $message
     * @return mixed
     * @throws \Exception
     */
    public function sendMessageEncrypted($from, $to, $message) {

        // nachricht verpacken
        $data = LibsodiumHelper::proccessUncencryptedMessage($message);

        // keypair generieren
        $senderPrivateKey = $this->configuration->getPrivateKeyForId($from);
        $recipientPublicKey = $this->getPublicKeyForId($to, $from);
        $keyPair = LibsodiumHelper::generateBoxKeyPair($senderPrivateKey, $recipientPublicKey);

        // nonce generieren
        $nonce = LibsodiumHelper::generateNonce();

        // daten verschlüsseln
        $box = LibsodiumHelper::makeBox($keyPair, $nonce, $data);

        // secret aus config lesen
        $secret = $this->configuration->getSecretForId($from);

        // hex2bin für http post
        $nonce = LibsodiumHelper::bin2hex($nonce);
        $box = LibsodiumHelper::bin2hex($box);

        return CurlHelper::post('https://msgapi.threema.ch/send_e2e', [
            'from' => $from,
            'to' => $to,
            'nonce' => $nonce,
            'box' => $box,
            'secret' => $secret,
        ]);

    }

    public function sendMessage($from, $to, $message) {

        $secret = $this->configuration->getSecretForId($from);

        return CurlHelper::post('https://msgapi.threema.ch/send_simple', [
            'from' => $from,
            'to' => $to,
            'text' => $message,
            'secret' => $secret
        ]);

    }


    /**
     * @param $from
     * @param $to
     * @param $messageId
     * @param $date
     * @param $nonce
     * @param $box
     * @param $mac
     * @param $nickname
     * @return array
     * @throws \R7\CORE\Exception
     */
    public function decryptMessage($from, $to, $messageId, $date, $nonce, $box, $mac, $nickname) {

        $nonce = LibsodiumHelper::hex2bin($nonce);
        $box = LibsodiumHelper::hex2bin($box);

        $recipientPrivateKey = $this->configuration->getPrivateKeyForId($to);
        $senderPublicKey = $this->getPublicKeyForId($from, $to);

        $decryptionKey = LibsodiumHelper::generateBoxKeyPair($recipientPrivateKey, $senderPublicKey);
        $decryptedMessage = LibsodiumHelper::openBox($decryptionKey, $nonce, $box);

        $message = LibsodiumHelper::processEncryptedMessage($decryptedMessage);

        return [
            'messageId' => $messageId,
            'date' => $date,
            'data' => $message['data'],
            'type' => $message['type'],
            'nickname' => $nickname,
            'from' => $from,
            'to' => $to,
        ];

    }

}
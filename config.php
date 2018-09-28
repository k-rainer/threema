<?php

class Config {

    /**
     * @return array
     */
    public function getThreemaIDSettings() {
        return [
            '*XXXXXXX' => [
                'secret'  => 'x',
                'private' => 'x',
                'public'  => 'x'
            ],
            
        ];
    }

    public function getSecretForId($id) {
        $allSettings = $this->getThreemaIDSettings();
        $idSettings = $allSettings[$id];
        return $idSettings['secret'];
    }

    public function getPrivateKeyForId($id) {
        $allSettings = $this->getThreemaIDSettings();
        $idSettings = $allSettings[$id];
        return LibsodiumHelper::hex2bin($idSettings['private']);
    }

    public function getPublicKeyForId($id) {
        $allSettings = $this->getThreemaIDSettings();
        $idSettings = $allSettings[$id];
        return LibsodiumHelper::hex2bin($idSettings['public']);
    }

}
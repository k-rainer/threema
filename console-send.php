<?php

require_once __DIR__ . '/threema.php';

$threema = new Threema();

var_dump($threema->sendMessageEncrypted('*INFODE1', 'VZ4ZY4Y6', 'Test 123'));
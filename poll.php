<?php

require_once 'vendor/autoload.php';

use Tiptone\Gbest\Belmont;
use Twilio\Rest\Client as TwilioClient;
use Twilio\Exceptions\ConfigurationException;
use GuzzleHttp\Client as GuzzleClient;

$sid = getenv('GBEST_SID');
$token = getenv('GBEST_TOKEN');

try {
    $twilio = new TwilioClient($sid, $token);
} catch (ConfigurationException $e) {
    echo $e->getMessage();
    exit(1);
}

$guzzle = new GuzzleClient();

$belmont = new Belmont($guzzle, $twilio);
$belmont->loadItems(__DIR__ . '/data/Belmont.json');

try {
    $belmont->search();
} catch (Exception $e) {
    echo $e->getMessage();
    exit(1);
}

$belmont->saveItems();
<?php

require_once 'vendor/autoload.php';

use Tiptone\Gbest\Belmont;
use Twilio\Rest\Client as TwilioClient;
use Twilio\Exceptions\ConfigurationException;
use GuzzleHttp\Client as GuzzleClient;

$db = new SQLite3(__DIR__ . '/data/sites.db');

$sid = TWILIO_SID;
$token = TWILIO_TOKEN;

try {
    $twilio = new TwilioClient($sid, $token);
} catch (ConfigurationException $e) {
    echo $e->getMessage();
    exit(1);
}

$guzzle = new GuzzleClient();

$sql = 'select id,
          name,
          url
        from sites';

$results = $db->query($sql);

if ($results->numColumns()) {
    while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
        $sites[] = $row;
    }
}

$sql = 'select id,
          name
        from items
        where siteid = :siteid';

$itemStatement = $db->prepare($sql);

$sql = 'insert into items (siteid, name) values (:siteid, :name)';

$itemInsertStatement = $db->prepare($sql);

foreach ($sites as $site) {
    $items = [];

    $itemStatement->bindParam(':siteid', $site['id']);
    $itemResult = $itemStatement->execute();

    if ($itemResult->numColumns()) {
        while ($row = $itemResult->fetchArray(SQLITE3_ASSOC)) {
            $items[] = $row['name'];
        }
    }
    $itemResult->finalize();

    $siteInstance = new $site['name']($guzzle);
    $siteInstance->setItems($items);

    $newItems = $siteInstance->search();

    foreach ($newItems as $newItem) {
        // send notification
        try {
            $twilio->messages->create(
                GBEST_CONTACT,
                [
                    'body' => sprintf('%s/%s', $site['url'], $newItem),
                    'from' => SID_PHONE
                ]
            );
        } catch (TwilioException $e) {
            echo $e->getMessage();
            exit(1);
        }

        // add item to database
        $itemInsertStatement->bindParam(':siteid', $site['id']);
        $itemInsertStatement->bindParam(':name', $newItem);
        $itemInsertStatement->execute();
    }
}

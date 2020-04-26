<?php

require_once 'vendor/autoload.php';

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
          url,
          pattern,
          item_url,
          content
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

$sql = 'select phone
        from client
            join subscription on
                client.id = clientid
        where siteid = :siteid';

$contactStatment = $db->prepare($sql);

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

    $res = $guzzle->request('GET', $site['url']);

    if ($site['content'] == 'json') {
        $json = json_decode($res->getBody());

        $doc = new \DOMDocument();
        $doc->loadHTML($json->Content);
    } else {
        $body = $res->getBody();

        $doc = new DOMDocument();
        $doc->loadHTML($body);
    }

    $nodes = $doc->getElementsByTagName('a');

    $newItems = [];

    foreach ($nodes as $node) {
        foreach ($node->attributes as $attr) {
            if ($attr->name == 'href') {
                if (substr($attr->value, 0, strlen($site['pattern'])) == $site['pattern']) {
                    $item = substr($attr->value, strlen($site['pattern']));

                    if (!in_array($item, $items) && trim($item) != '') {
                        $items[] = $item;
                        $newItems[] = $item;
                    }
                }
            }
        }
    }

    foreach ($newItems as $newItem) {
        // send notification(s)
        $contactStatment->bindParam(':siteid', $site['id']);
        $contactResult = $contactStatment->execute();

        if ($contactResult->numColumns()) {
            while ($row = $contactResult->fetchArray(SQLITE3_ASSOC)) {
                try {
                    $twilio->messages->create(
                        $row['phone'],
                        [
                            'body' => sprintf('%s/%s', $site['item_url'], $newItem),
                            'from' => SID_PHONE
                        ]
                    );
                } catch (TwilioException $e) {
                    echo $e->getMessage();
                    exit(1);
                }
            }
        }
        $contactResult->finalize();

        // add item to database
        $itemInsertStatement->bindParam(':siteid', $site['id']);
        $itemInsertStatement->bindParam(':name', $newItem);
        $itemInsertStatement->execute();
    }
}

<?php

require_once 'vendor/autoload.php';

$known = json_decode(file_get_contents('known.json'));

$client = new GuzzleHttp\Client();
$res = $client->request('GET', 'http://belmont.limitedrun.com/');

$body = $res->getBody();

$doc = new DOMDocument();
$doc->loadHTML($body);

$nodes = $doc->getElementsByTagName('a');

foreach ($nodes as $node) {
    if ($node->attributes->getNamedItem('class')) {
        continue;
    }

    foreach ($node->attributes as $attr) {
        if ($attr->name == 'href') {
            // /products/620077-knife-tee-white
            if (substr($attr->value, 0, 10) == '/products/') {
                $item = substr($attr->value, 10);

                if (!in_array($item, $known)) {
                    // create the URL for the item
                    $itemUrl = sprintf('http://belmont.limitedrun.com/products/%s', $item);

                    // email notification
                    $tr = new Zend_Mail_Transport_Smtp('extsmtp.shsu.edu');
                    Zend_Mail::setDefaultTransport($tr);

                    $mail = new Zend_Mail();
                    $mail->setBodyText($itemUrl);
                    $mail->setFrom('noreply@shsu.edu');
                    $mail->addTo('9182779147@vtext.com');
                    $mail->addBcc('9367141591@txt.att.net');
                    $mail->setSubject('New Item');
                    $mail->send();

                    // add the item to avoid duplicate notifications
                    $known[] = $item;

                    echo sprintf('New item added %s', $item) . PHP_EOL;
                }
            }
        }
    }
}

file_put_contents('known.json', json_encode($known));


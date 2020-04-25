<?php
namespace Tiptone\Gbest;

use Twilio\Exceptions\TwilioException;

class Belmont extends Site
{
    public function search()
    {
        $res = $this->guzzle->request('GET', 'http://belmont.limitedrun.com/');

        $body = $res->getBody();

        $doc = new \DOMDocument();
        $doc->loadHTML($body);

        $nodes = $doc->getElementsByTagName('a');

        foreach ($nodes as $node) {
            foreach ($node->attributes as $attr) {
                if ($attr->name == 'href') {
                    if (substr($attr->value, 0, 10) == '/products/') {
                        $item = substr($attr->value, 10);

                        if (!in_array($item, $this->items)) {
                            try {
                                $this->twilio->messages->create(
                                    GBEST_CONTACT,
                                    [
                                        'body' => sprintf('http://belmont.limitedrun.com/products/%s', $item),
                                        'from' => TIPTONE_PHONE
                                    ]
                                );
                            } catch (TwilioException $e) {
                                throw new \Exception($e->getMessage());
                            }

                            // add the item to avoid duplicate notifications
                            $this->items[] = $item;
                        }
                    }
                }
            }
        }
    }
}
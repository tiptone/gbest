<?php
namespace Tiptone\Gbest;

class BlueSwanRecords extends Site
{
    public function search()
    {
        $res = $this->guzzle->request('GET', 'https://blueswanrecords.bigcartel.com/');

        $body = $res->getBody();

        $doc = new \DOMDocument();
        $doc->loadHTML($body);

        $nodes = $doc->getElementsByTagName('a');

        $newItems = [];

        foreach ($nodes as $node) {
            foreach ($node->attributes as $attr) {
                if ($attr->name == 'href') {
                    if (substr($attr->value, 0, 9) == '/product/') {
                        $item = substr($attr->value, 9);

                        if (!in_array($item, $this->items) && trim($item) != '') {
                            // add the item to avoid duplicate notifications
                            $this->items[] = $item;

                            $newItems[] = $item;
                        }
                    }
                }
            }
        }

        return $newItems;
    }
}
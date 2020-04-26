<?php
namespace Tiptone\Gbest;

class CityAndColor extends Site
{
    public function search()
    {
        $res = $this->guzzle->request('GET', 'https://cityandcolour.store-08.com/music/vinyl/');

        $body = $res->getBody();

        $doc = new \DOMDocument();
        $doc->loadHTML($body);

        $nodes = $doc->getElementsByTagName('a');

        $newItems = [];

        foreach ($nodes as $node) {
            foreach ($node->attributes as $attr) {
                if ($attr->name == 'href') {
                    if (substr($attr->value, 0, 13) == '/music/vinyl/') {
                        $item = substr($attr->value, 13);

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
<?php
namespace Tiptone\Gbest;

class DgdBundles extends Site
{
    public function search()
    {
        $res = $this->guzzle->request('GET', 'https://merchnow.com/catalogs/showList?type=bundles&name=dance-gavin-dance&offset=0&limit=256&format=json');

        $json = json_decode($res->getBody());

        $doc = new \DOMDocument();
        $doc->loadHTML($json->Content);

        $nodes = $doc->getElementsByTagName('a');

        $newItems = [];

        foreach ($nodes as $node) {
            foreach ($node->attributes as $attr) {
                if ($attr->name == 'href') {
                    if (substr($attr->value, 0, 13) == '/products/v2/') {
                        $item = substr($attr->value, 13);

                        if (!in_array($item, $this->items)) {
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
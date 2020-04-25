<?php
namespace Tiptone\Gbest;

use GuzzleHttp\Client;

class Site implements SiteWatcher
{
    /**
     * @var Client
     */
    protected $guzzle;

    protected $items = [];

    private $itemHash;
    private $itemPath;

    public function __construct(Client $guzzle)
    {
        $this->guzzle = $guzzle;
    }

    public function search()
    {}

    public function setItems($items)
    {
        $this->items = $items;
    }
}
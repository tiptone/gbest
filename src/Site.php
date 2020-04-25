<?php
namespace Tiptone\Gbest;

use GuzzleHttp\Client as GuzzleClient;
use Twilio\Rest\Client as TwilioClient;

class Site implements SiteWatcher
{
    /**
     * @var GuzzleClient
     */
    protected $guzzle;

    /**
     * @var TwilioClient
     */
    protected $twilio;

    protected $items = [];

    private $itemHash;
    private $itemPath;

    public function __construct(GuzzleClient $guzzle, TwilioClient $twilio)
    {
        $this->guzzle = $guzzle;
        $this->twilio = $twilio;
    }

    public function search()
    {}

    public function loadItems($infile)
    {
        $this->itemPath = $infile;

        $json = file_get_contents($infile);

        $this->itemHash = sha1($json);

        $this->items = json_decode($json);
    }

    public function saveItems()
    {
        $json = json_encode($this->items);

        $newHash = sha1($json);

        if ($newHash != $this->itemHash) {
            file_put_contents($this->itemPath,  $json);
        }
    }
}
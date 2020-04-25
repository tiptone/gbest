<?php
namespace Tiptone\Gbest;

interface SiteWatcher
{
    public function search();
    public function setItems($items);
}
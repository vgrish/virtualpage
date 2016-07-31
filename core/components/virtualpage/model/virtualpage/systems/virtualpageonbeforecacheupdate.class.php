<?php


class virtualpageOnBeforeCacheUpdate extends virtualpagePlugin
{
    public function run()
    {
        $this->virtualpage->clearCache();
    }
}
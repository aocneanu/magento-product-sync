<?php

namespace LaravelEnso\MagentoProductSync\Cron;

use LaravelEnso\MagentoProductSync\Service\Sync as Service;

class Sync
{
    public function execute()
    {
        (new Service())
            ->handle();
    }
}
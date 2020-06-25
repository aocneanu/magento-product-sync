<?php

namespace LaravelEnso\MagentoProductSync\Service;

use LaravelEnso\MagentoProductSync\Model\Directors\Product as Director;
use LaravelEnso\MagentoProductSync\Repositories\ProductRepository;
use Magento\Framework\App\ObjectManager;
use Throwable;

class Sync
{
    private $api;
    private $progress;
    private $imported;
    private $repository;

    public function __construct($progress = null)
    {
        $this->api = new Api();
        $this->initProgress($progress);
        $this->repository = ProductRepository::getInstance();
    }

    public function handle()
    {
        $this->repository->loadBySkues($this->api->keys());

        foreach ($this->api->updated() + $this->api->created() as $external) {
            try {
                (new Director($external, $this->repository->getOrNew($external['sku'])))
                    ->make()
                    ->save();
            } catch (Throwable $e) {
                ObjectManager::getInstance()
                    ->get('\Psr\Log\LoggerInterface')
                    ->error($e);
            }

            $this->done($external);
        }

        foreach ($this->api->removed() as $external) {
            $this->repository->delete($external['sku']);
            $this->done($external);
        }
    }

    protected function done($external)
    {
        $this->api->synced($external);
        $this->imported++;

        if ($this->progress) {
            $this->progress->advance();
        }
    }

    private function initProgress($progress = null)
    {
        if ($progress) {
            $this->progress = $progress;
            $this->progress->setMaxSteps($this->api->count());
        }
    }
}

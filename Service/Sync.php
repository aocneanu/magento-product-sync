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

        $this->create()
            ->update()
            ->delete();
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

    protected function error($e)
    {
        ObjectManager::getInstance()
            ->get('\Psr\Log\LoggerInterface')
            ->error($e);
    }


    private function create(): self
    {
        foreach ($this->api->created() as $external) {
            try {
                (new Director($external, $this->repository->getOrNew($external['sku'])))
                    ->make()
                    ->save();
            } catch (Throwable $e) {
                $this->error($e);
                $this->error(print_r($external, true));
            }

            $this->done($external);
        }

        return $this;
    }


    private function update(): self
    {
        foreach ($this->api->updated() as $external) {
            try {
                (new Director($external, $this->repository->getOrNew($external['sku'])))
                    ->sync()
                    ->save();
            } catch (Throwable $e) {
                $this->error($e);
                $this->error(print_r($external, true));
            }

            $this->done($external);
        }

        return $this;
    }

    private function delete(): void
    {
        foreach ($this->api->removed() as $external) {
            $this->repository->delete($external['sku']);
            $this->done($external);
        }
    }
}

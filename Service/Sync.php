<?php

namespace LaravelEnso\MagentoProductSync\Service;

use LaravelEnso\MagentoProductSync\Model\Directors\Product as Director;
use LaravelEnso\MagentoProductSync\Repositories\Finders\Product as Finder;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\ObjectManager;

class Sync
{
    private $api;
    private $products;
    private $progress;
    private $imported;

    public function __construct($progress = null)
    {
        $this->api = new Api();
        $this->initProducts();
        $this->initProgress($progress);
    }

    public function handle()
    {
        foreach ($this->api->updated() + $this->api->created() as $external) {
            $start = microtime(true);

            try {
                $product = (new Director($external, $this->products[$external['sku']] ?? null))
                    ->make()
                    ->save();
            } catch (\Throwable $e) {
                print_r($external);
                echo $e;
                return;
            }

            $this->api->synced($external);

            $this->progress();
        }

        foreach ($this->api->removed() as $external) {

            $start = microtime(true);

            if(isset($this->products[$external['sku']])) {
                ObjectManager::getInstance()->create(ProductRepository::class)
                    ->delete($this->products[$external['sku']]);
            }

            $this->api->synced($external);
            $this->progress();
        }
    }

    protected function progress()
    {
        $this->imported++;

        if ($this->progress) {
            $this->progress->advance();
        }
    }

    private function initProducts()
    {
        $this->products = (new Finder())
            ->getWhereInSku($this->api->keys());
    }

    private function initProgress($progress = null)
    {
        if ($progress) {
            $this->progress = $progress;
            $this->progress->setMaxSteps($this->api->count());
        }
    }
}
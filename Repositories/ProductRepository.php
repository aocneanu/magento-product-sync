<?php

namespace LaravelEnso\MagentoProductSync\Repositories;

use LaravelEnso\MagentoProductSync\Repositories\Finders\Product as Finder;
use Magento\Catalog\Model\Product as Model;
use Magento\Catalog\Model\ProductRepository as BaseRepository;
use Magento\Framework\App\ObjectManager;

class ProductRepository
{
    private static $instance;

    private $products;

    public static function getInstance()
    {
        return static::$instance = static::$instance
            ?? new static();
    }

    public function loadBySkues($SKUes)
    {
        $collection = (new Finder())
            ->getWhereIn('sku', $SKUes);

        $this->products = [];

        foreach ($collection as $item) {
            $this->products[$item->getSku()] = $item;
        }
    }

    public function getOrNew($sku)
    {
        return $this->products[$sku]
            ?? ObjectManager::getInstance()->create(Model::class);
    }

    public function delete($sku)
    {
        if (isset($this->products[$sku])) {
            ObjectManager::getInstance()->create(BaseRepository::class)
                ->delete($this->products[$sku]);
        }
    }
}
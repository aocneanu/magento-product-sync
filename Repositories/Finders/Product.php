<?php

namespace LaravelEnso\MagentoProductSync\Repositories\Finders;

use Magento\Catalog\Model\Product as Model;

class Product extends Finder
{
    public function __construct()
    {
        parent::__construct(Model::class);
    }

    public function getWhereInSku($values)
    {
        $collection = $this->getWhereIn('sku', $values);

        $result = [];

        foreach ($collection as $item) {
            $result[$item->getSku()] = $item;
        }

        return $result;
    }
}
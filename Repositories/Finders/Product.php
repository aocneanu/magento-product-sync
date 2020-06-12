<?php

namespace LaravelEnso\MagentoProductSync\Repositories\Finders;

use Magento\Catalog\Model\Product as Model;

class Product extends Finder
{
    public function __construct()
    {
        parent::__construct(Model::class);
    }
}
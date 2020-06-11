<?php

namespace LaravelEnso\MagentoProductSync\Repositories\Finders;

use Magento\Catalog\Model\Category as Model;

class Category extends Finder
{
    public function __construct()
    {
        parent::__construct(Model::class);
    }
}
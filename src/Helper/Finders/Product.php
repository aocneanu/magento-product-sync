<?php

namespace LaravelEnso\MagentoProductSync\Helper\Finders;

class Product extends Finder
{
    public function __construct()
    {
        parent::__construct('\Magento\Catalog\Model\Product');
    }
}
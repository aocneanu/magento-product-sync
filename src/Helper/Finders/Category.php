<?php

namespace LaravelEnso\MagentoProductSync\Helper\Finders;

class Category extends Finder
{
    public function __construct()
    {
        parent::__construct('Magento\Catalog\Model\Category');
    }
}
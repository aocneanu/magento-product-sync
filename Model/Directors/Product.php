<?php

namespace LaravelEnso\MagentoProductSync\Model\Directors;

use LaravelEnso\MagentoProductSync\Repositories\ManufacturerRepository;

class Product
{
    private const SearchAndCatalog = '4';
    private const DefaultAttributeID = '4';
    private const Enable = '1';

    private $external;
    private $instance;
    private $category;

    public function __construct($external, $instance)
    {
        $this->external = $external;
        $this->instance = $instance;
    }

    public function make()
    {
        $this->init()
            ->price()
            ->enableForSearchAndCatalog()
            ->quantity()
            ->defaultAttributeSet()
            ->description()
            ->shortDescription()
            ->manufacturer()
            ->enable()
            ->categories()
            ->url()
            ->image();

        return $this->instance;
    }

    private function image()
    {
        if ($this->external['image']) {
            (new Image($this->instance, $this->external['image']))
                ->addImageToProduct();
        }
    }

    private function formatPrice()
    {
        return sprintf('%0.6f', $this->external['price']);
    }

    private function categories()
    {
        $this->category = (new Category($this->external['category']))
            ->createCategories();

        if (! in_array($this->category->getId(), $this->instance->getCategoryIds())) {
            $this->instance->setCategoryIds([$this->category->getId()]);
        }

        return $this;
    }

    private function price()
    {
        $this->instance->setPrice($this->formatPrice());

        return $this;
    }

    private function enableForSearchAndCatalog()
    {
        $this->instance->setVisibility(self::SearchAndCatalog);

        return $this;
    }

    private function quantity()
    {
        $this->instance ->setQuantityAndStockStatus([
            'is_in_stock' => '1',
            'qty' => ''. $this->external['stock'],
        ]);

        return $this;
    }

    private function defaultAttributeSet()
    {
        $this->instance->setAttributeSetId(self::DefaultAttributeID);

        return $this;
    }

    private function enable()
    {
        $this->instance->setStatus(self::Enable);

        return $this;
    }


    private function url()
    {
        $path = parse_url($this->external['url'])['path'];
        $path = strtolower(ltrim($path, '/'));
        $categoryPath = str_replace(' ', '-', strtolower($this->category->getName()));

        if($path === $categoryPath) {
            $path = 'product-' . $path;
        }

        $this->instance->setUrlKey($path);

        return $this;
    }

    private function init()
    {
        $this->instance->setSku($this->external['sku'])
            ->setName($this->external['name'])
            ->setTypeId('simple')
            ->setWebsiteIds([1]);

        return $this;
    }

    private function shortDescription()
    {
        if (is_string($this->external['short_description'])) {
            $this->instance->setShortDescription($this->external['short_description']);
        }

        return $this;
    }

    private function manufacturer()
    {
        $manufacturerId = ManufacturerRepository::getInstance()
            ->getOrCreate($this->external['manufacturer']);

        $this->instance->setManufacturer($manufacturerId);

        return $this;
    }

    private function description()
    {
        if (is_string($this->external['description'])) {
            $this->instance->setDescription($this->external['description']);
        }

        return $this;
    }
}

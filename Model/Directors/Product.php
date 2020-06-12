<?php

namespace LaravelEnso\MagentoProductSync\Model\Directors;

use LaravelEnso\MagentoProductSync\Repositories\ManufacturerRepository;
use Magento\Catalog\Model\Product as Model;
use Magento\Framework\App\ObjectManager;
use function GuzzleHttp\Psr7\str;

class Product
{
    private const SearchAndCatalog = '4';
    private const DefaultAttributeID = '4';
    private const Enable = '1';

    private $external;
    private $instance;

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
            ->qunatity()
            ->defaultAttributeSet()
            ->description()
            ->shortDescription()
            ->manufacturer()
            ->url()
            ->enable()
            ->setCategories()
            ->addsImage();

        return $this->instance;
    }

    private function addsImage()
    {
        (new Image($this->instance, $this->external['image']))
            ->addImagesToProduct();
    }

    /**
     * @return string
     */
    private function formatPrice(): string
    {
        return sprintf('%0.6f', $this->external['price']);
    }

    private function setCategories()
    {
        $categoryDirector = (new Category())
            ->createCategory(explode('>', $this->external['category']));

        if (array_diff($categoryDirector, $this->instance->getCategoryIds())) {
            $this->instance->setCategoryIds($categoryDirector);
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

    private function qunatity()
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
        $this->instance->setUrlKey(ltrim($path, '/'));

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
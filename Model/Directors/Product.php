<?php

namespace LaravelEnso\MagentoProductSync\Model\Directors;

use Magento\Catalog\Model\Product as Model;
use Magento\Framework\App\ObjectManager;

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
        $this->instance = $instance
            ?? ObjectManager::getInstance()->create(Model::class);
    }

    public function make()
    {
        $this->init()
            ->setPrice()
            ->enableForSearchAndCatalog()
            ->setQuantity()
            ->setDefaultAttributeSet()
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

    private function setPrice()
    {
        $this->instance->setPrice($this->formatPrice());

        return $this;
    }

    private function enableForSearchAndCatalog()
    {
        $this->instance->setVisibility(self::SearchAndCatalog);

        return $this;
    }

    private function setQuantity()
    {
        $this->instance ->setQuantityAndStockStatus([
            'is_in_stock' => '1',
            'qty' => ''. $this->external['stock'],
        ]);

        return $this;
    }

    private function setDefaultAttributeSet()
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
            ->setUrlKey(ltrim(parse_url($this->external['url'])['path'], '/'))
            ->setTypeId('simple')
            ->setDescription($this->string($this->external['description']))
            ->setShortDescription($this->string($this->external['short_description']))
            ->setManufactur($this->external['manufacture']);

        return $this;
    }

    private function string($str)
    {
        return is_string($str) ? $str : '';
    }
}
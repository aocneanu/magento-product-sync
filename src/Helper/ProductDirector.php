<?php

namespace LaravelEnso\MagentoProductSync\Helper;

use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Filesystem;
use Magento\Store\Model\StoreManagerInterface;
use LaravelEnso\MagentoProductSync\Helper\Finders\Product;
use LaravelEnso\MagentoProductSync\Model\Directors\Category;

class ProductDirector
{
    const SEARCH_AND_CATALOG = '4';
    const DEFAULT_ATTRIBUTE_ID = '4';
    const ENABLE = '1';
    private $external;
    private $instance;

    protected $_productFactory;
    protected $_categoryFactory;
    protected $_fileSystem;
    protected $_objectManager;
    protected $_storeManager;
    protected $_store;

    public function __construct($external)
    {
        $this->external = $external;
        $this->instance = (new Product())->findOrNew(['sku' => $external->CodProdus]);
        $this->_objectManager = ObjectManager::getInstance();
        $this->_productFactory = $this->_objectManager->create(ProductFactory::class);
        $this->_categoryFactory = $this->_objectManager->create(CategoryFactory::class);
        $this->_fileSystem = $this->_objectManager->create(Filesystem::class);
        $this->_storeManager = $this->_objectManager->create(StoreManagerInterface::class);
        $this->_store = $this->_storeManager->getStore();
    }

    public function make()
    {
        $this->init()
            ->setPrice()
            ->enableForSearchAndCatalog()
            ->setQuantity()
            ->setDefaultAttributeSet()
            ->enable()
            ->setCategories()
            ->addsImage();

        return $this->instance;
    }

    private function addsImage()
    {
        try {
            (new Image($this->external->URL_poza))
                ->addImagesToProduct($this->instance);
        } catch (\Throwable $e) {
        }

        return $this;
    }

    /**
     * @return string
     */
    private function formatPrice(): string
    {
        return sprintf('%0.6f', $this->external->PretEndUser);
    }

    private function setCategories()
    {
        $categoryDirector = (new Category())
            ->createCategory(explode('>', $this->external->NumeCategorie));

        if (array_diff($categoryDirector, $this->instance->getCategoryIds())) {
            $this->instance->setCategoryIds($categoryDirector); // price of product
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
        $this->instance->setVisibility(self::SEARCH_AND_CATALOG); // visibilty of product (catalog / search / catalog, search / Not visible individually)

        return $this;
    }

    private function setQuantity()
    {
        $this->instance ->setQuantityAndStockStatus([
            'is_in_stock' => $this->external->Stoc > 0 ? '1' : '0',
            'qty' => ''. $this->external->Stoc,
        ]);

        return $this;
    }

    private function setDefaultAttributeSet()
    {
        $this->instance->setAttributeSetId(self::DEFAULT_ATTRIBUTE_ID);

        return $this;
    }

    private function enable()
    {
        $this->instance->setStatus(self::ENABLE); // 1->En 0->Disable, default disable

        return $this;
    }

    private function init()
    {
        $this->instance->setSku($this->external->CodProdus)
            ->setName($this->external->NumeProdus)
            ->setTypeId('simple')
            ->setDescription($this->external->Descriere)
            ->setShortDescription($this->external->DescriereScurta);

        return $this;
    }
}
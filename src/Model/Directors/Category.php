<?php

namespace LaravelEnso\MagentoProductSync\Model\Directors;

use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use LaravelEnso\MagentoProductSync\Helper\Finders\Category as Finder;

class Category
{
    private $finder;

    protected $_productFactory;
    protected $_categoryFactory;
    protected $_objectManager;
    protected $_storeManager;
    protected $_store;

    public function __construct()
    {
        $this->finder = new Finder();
        $this->_objectManager = ObjectManager::getInstance();
        $this->_storeManager = $this->_objectManager->create(StoreManagerInterface::class);
        $this->_store = $this->_storeManager->getStore();
    }

    public function createCategory($cats, $root = null)
    {
        $root = $root ?? $this->finder
                ->findOrNew($this->_store->getRootCategoryId());

        if ($cats === []) {
            return [$root->getId()];
        }

        $name = ucfirst(array_shift($cats));

        $category = $this->finder->findOrNew(['name' => $name]);

        if (! $category->getId()) {
            $category = $category->setName($name)
                ->setIsActive(true)
                ->setData('description', $name)
                ->setParentId($root->getId())
                ->setStoreId($this->storeId())
                ->setPath($root->getPath())
                ->save();
        }

        return $this->createCategory($cats, $category);
    }

    private function storeId()
    {
        return $this->_store->getStoreId();
    }
}
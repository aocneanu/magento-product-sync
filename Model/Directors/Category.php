<?php

namespace LaravelEnso\MagentoProductSync\Model\Directors;

use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use LaravelEnso\MagentoProductSync\Repositories\Finders\Category as Finder;

class Category
{
    private $finder;
    protected $_store;

    public function __construct()
    {
        $this->finder = new Finder();
        $this->initStore();
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

    private function initStore(): void
    {
        $storeManager = ObjectManager::getInstance()
            ->create(StoreManagerInterface::class);

        $this->_store = $storeManager->getStore();
    }
}
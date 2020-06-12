<?php

namespace LaravelEnso\MagentoProductSync\Model\Directors;

use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use LaravelEnso\MagentoProductSync\Repositories\Finders\Category as Finder;

class Category
{
    private $finder;
    private $categories;

    private $_store;

    public function __construct($cats)
    {
        $this->finder = new Finder();
        $this->initStore();
        $this->categories = explode('>', $cats);
    }

    public function createCategories($parent = null)
    {
        $parent = $parent ?? $this->root();

        if (count($this->categories) === 1) {
            return $this->getOrCreate($parent);
        }

        return $this->createCategories($this->getOrCreate($parent));
    }

    private function getOrCreate($parent)
    {
        $name = ucfirst(array_shift($this->categories));
        $category = $this->finder->findOrNew(['name' => $name]);

        if ($category->getId()) {
            return $category;
        }

        return $category->setName($name)
            ->setIsActive(true)
            ->setData('description', $name)
            ->setParentId($parent->getId())
            ->setUrlKey($this->urlKey($name))
            ->setStoreId($this->storeId())
            ->setPath($parent->getPath())
            ->save();
    }

    private function urlKey($name)
    {
        $url = strtolower($name);
        $urlDecode = urldecode(html_entity_decode(strip_tags($url)));
        $cleanUrl = preg_replace('/ +/', '', preg_replace('/[^A-Za-z0-9 ]/', '', $urlDecode));

        return trim($cleanUrl);
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

    private function root()
    {
        return $this->finder
            ->findOrNew($this->_store->getRootCategoryId());
    }
}
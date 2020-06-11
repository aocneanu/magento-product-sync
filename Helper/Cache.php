<?php

namespace LaravelEnso\MagentoProductSync\Helper;

use Exception;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Filesystem;

class Cache
{
    private $_fileSystem;

    public function __construct()
    {
        $this->_fileSystem = ObjectManager::getInstance()
            ->create(Filesystem::class);
    }

    public function getCache() //TODO :: RENAME IT!
    {
        try {
            return json_decode(file_get_contents($this->path()), true) ?? [];
        } catch (Exception $e) {
        }

        return [];
    }

    public function updated($product)
    {
        $cache = $this->getCache();

        $cache[''.$product['sku']] = md5(json_encode($product));

        file_put_contents($this->path(), json_encode($cache));
    }

    public function deleted($product)
    {
        $cache = $this->getCache();

        unset($cache[$product['sku']]);

        file_put_contents($this->path(), json_encode($cache));
    }

    public function isImported($product)
    {
        $checksum = $this->getCache()[$product['sku']] ?? null;

        return md5(json_encode($product)) === $checksum;
    }

    private function path()
    {
        return $this->_fileSystem->getDirectoryRead(DirectoryList::CACHE)
            ->getAbsolutePath("import.json");
    }
}
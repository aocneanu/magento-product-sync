<?php

namespace LaravelEnso\MagentoProductSync\Helper;

use LaravelEnso\MagentoProductSync\Model\Cache\Type;
use Magento\Framework\App\ObjectManager;

class Cache
{
    private $_cacheType;
    private $data;

    public function __construct()
    {
        $this->_cacheType = ObjectManager::getInstance()
            ->create(Type::class);

        $this->load();
    }

    public function all()
    {
        return $this->data;
    }

    public function put($key, $value)
    {
        $this->data[$key] = $value;

        $this->save();
    }

    public function get($key)
    {
        return $this->data[$key] ?? null;
    }

    public function delete($key)
    {
        unset($this->data[$key]);

        $this->save();
    }

    private function load()
    {
        $result = $this->_cacheType
            ->load(Type::IMPORT_CACHE_KEY);

        $this->data = json_decode($result, true) ?? [];
    }

    private function save()
    {
        $this->_cacheType->save(json_encode($this->data), Type::IMPORT_CACHE_KEY);
    }
}
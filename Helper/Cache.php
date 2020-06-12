<?php

namespace LaravelEnso\MagentoProductSync\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Filesystem;

class Cache
{
    private $_fileSystem;
    private $data;

    public function __construct()
    {
        $this->_fileSystem = ObjectManager::getInstance()
            ->create(Filesystem::class);

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
        if (file_exists($this->path())) {
            $this->data = json_decode(file_get_contents($this->path()), true) ?? [];
        }
    }

    private function path()
    {
        return $this->_fileSystem->getDirectoryRead(DirectoryList::CACHE)
            ->getAbsolutePath("import.json");
    }

    private function save()
    {
        file_put_contents($this->path(), json_encode($this->data));
    }
}
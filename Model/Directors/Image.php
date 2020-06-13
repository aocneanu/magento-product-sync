<?php

namespace LaravelEnso\MagentoProductSync\Model\Directors;

use Magento\Catalog\Model\Product\Gallery\ReadHandler;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Filesystem;
use Psr\Log\LoggerInterface;
use Throwable;

class Image
{
    private $url;
    protected $_fileSystem;
    protected $product;

    public function __construct($product, $url)
    {
        $this->url = $url;
        $this->product = $product;
        $this->_fileSystem = ObjectManager::getInstance()
            ->create(Filesystem::class);
    }

    public function addImageToProduct()
    {
        $this->loadGalleries();

        if ($this->getCurrentImages()[$this->name()] ?? false) {
            return;
        }

        try {
            $this->download();
        } catch (Throwable $e) {
            $this->logger()
                ->error('image download failed', ['exception' => $e]);

            return;
        }

        $this->product->addImageToMediaGallery(
            $this->path(), ['image', 'small_image', 'thumbnail'], false, false
        );
    }

    private function download()
    {
        if ($this->isNotExists()) {
            file_put_contents($this->path(), file_get_contents($this->url));
        }
    }

    private function name()
    {
        $parts = explode('/', $this->url);

        return $this->product->getSku() . '_' . end($parts);
    }

    private function path()
    {
        return $this->_fileSystem->getDirectoryRead(DirectoryList::MEDIA)
            ->getAbsolutePath("catalog/product/{$this->name()}");
    }

    private function isNotExists()
    {
        return ! file_exists($this->path());
    }

    private function getCurrentImages()
    {
        $images = [];

        foreach ($this->product->getMediaGalleryImages() as $mediaGalleryImage) {
            $images[$this->originalName($mediaGalleryImage->getFile())] = true;
        }

        return $images;
    }

    private function originalName($savedName)
    {
        $name = basename($savedName);

        return preg_replace('/(.*)(_\d+)(\.\w+)/', '$1$3', $name);
    }

    private function loadGalleries()
    {
        ObjectManager::getInstance()
            ->get(ReadHandler::class)->execute($this->product);
    }

    private function logger()
    {
        return ObjectManager::getInstance()
            ->get(LoggerInterface::class);
    }
}
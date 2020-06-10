<?php


namespace LaravelEnso\MagentoProductSync\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Filesystem;

class Image
{
    private $url;
    protected $_fileSystem;

    public function __construct($url)
    {
        $this->url = $url;
        $this->_fileSystem = ObjectManager::getInstance()
            ->create(Filesystem::class);
    }

    public function addImagesToProduct($product)
    {
        if (! $this->isNotExists()) { //TODO :: REMOVE IT
            return $product;
        }

        if ($this->alreadyAssigned($product)) {
            return $product;
        }

        $path = $this->download();

        $product->addImageToMediaGallery(
            $path, ['image', 'small_image', 'thumbnail'], false, false
        );

        return $product;
    }


    private function download()
    {
        if ($this->isNotExists()) {
            file_put_contents($this->path(), file_get_contents($this->url));
        }

        return $this->path();
    }

    private function name()
    {
        $parts = explode('/', $this->url);

        return end($parts);
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

    private function originalProductImageName($product)
    {
        $currentImage = array_reverse(explode('/', $product->getImage()))[0] ?? null;
        return preg_replace("/(.*)(_\d*)(\.\w+)/", "$1$3", $currentImage);
    }

    /**
     * @param $product
     * @return bool
     */
    private function alreadyAssigned($product): bool
    {
        return $this->name() == $this->originalProductImageName($product);
    }
}
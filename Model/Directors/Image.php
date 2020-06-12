<?php


namespace LaravelEnso\MagentoProductSync\Model\Directors;

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

    public function addImagesToProduct()
    {
        if ($this->isExists()) {
            return;
        }

        try {
            $this->download();
        } catch (Throwable $e) {
            ObjectManager::getInstance()
                ->get(LoggerInterface::class)
                ->error('image download failed', ['exception' => $e]);

            return;
        }

        $this->product->addImageToMediaGallery(
            $this->path(), ['image', 'small_image', 'thumbnail'], false, false
        );
    }

    private function download()
    {
        file_put_contents($this->path(), file_get_contents($this->url));
    }

    private function name()
    {
        $parts = explode('/', $this->url);

        return $this->product->getSku() . '_' .end($parts);
    }

    private function path()
    {
        return $this->_fileSystem->getDirectoryRead(DirectoryList::MEDIA)
            ->getAbsolutePath("catalog/product/{$this->name()}");
    }

    private function isExists()
    {
        return file_exists($this->path());
    }
}
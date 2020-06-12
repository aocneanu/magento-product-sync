<?php

namespace LaravelEnso\MagentoProductSync\Repositories;

use Magento\Catalog\Api\ProductAttributeOptionManagementInterface;
use Magento\Eav\Api\Data\AttributeOptionInterfaceFactory;
use Magento\Eav\Model\Entity\AttributeFactory;
use Magento\Framework\App\ObjectManager;

class ManufacturerRepository
{
    private static $instance;

    private $manufacturers;

    private $_attributeOptionManagement;
    private $_attributeOptionInterfaceFactory;
    private $_eav;

    public static function getInstance()
    {
        return static::$instance = static::$instance
            ?? new static();
    }

    public function __construct()
    {
        $this->_eav = ObjectManager::getInstance()->get(AttributeFactory::class);
        $this->_attributeOptionManagement = ObjectManager::getInstance()->get(ProductAttributeOptionManagementInterface::class);
        $this->_attributeOptionInterfaceFactory = ObjectManager::getInstance()->get(AttributeOptionInterfaceFactory::class);
    }

    public function getOrCreate($manufacturer)
    {
        return $this->get($manufacturer)
            ?? $this->create($manufacturer);
    }

    public function get($manufacturer)
    {
        $this->init();

        return $this->manufacturers[$manufacturer] ?? null;
    }

    public function create($manufacturer)
    {
        $option = $this->_attributeOptionInterfaceFactory->create();
        $option->setLabel($manufacturer);
        $result = $this->_attributeOptionManagement->add('manufacturer', $option);
        $id = str_replace('id_', '', $result);
        $this->manufacturers[$manufacturer] = $id;

        return $id;
    }

    private function init()
    {
        if ($this->manufacturers !== null) {
            return;
        }

        $attribute = $this->_eav->create()->load('manufacturer', 'attribute_code');
        $this->manufacturers = [];

        foreach ($this->_attributeOptionManagement->getItems('manufacturer') as $option) {
            $this->manufacturers[$option->getLabel()] = $option->getValue();
        }
    }

}
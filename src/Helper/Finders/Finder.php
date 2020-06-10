<?php

namespace LaravelEnso\MagentoProductSync\Helper\Finders;

use Magento\Framework\App\ObjectManager;

//TODO :: MOVE IT TO DATA NAMESPACE
class Finder
{
    private $modelClass;

    public function __construct($modelClass)
    {
        $this->modelClass = $modelClass;
    }

    public function findOrNew($conditions)
    {
        if (! is_array($conditions)) {
            $conditions = ['entity_id' => $conditions];
        }

        $collection = $this->instance()
            ->getCollection();

        foreach ($conditions as $attribute => $value) {
            $collection->addAttributeToFilter($attribute, ['eq' => $value]);
        }

        $result = $collection->load()->getFirstItem();

        return $result->getId()
            ? $result
            : $this->instance();
    }

    private function instance()
    {
        return ObjectManager::getInstance()
            ->create($this->modelClass);
    }
}
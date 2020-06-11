<?php

namespace LaravelEnso\MagentoProductSync\Repositories\Finders;

use Magento\Framework\App\ObjectManager;

class Finder
{
    private $modelClass;

    public function __construct($modelClass)
    {
        $this->modelClass = $modelClass;
    }

    public function findOrNew($conditions)
    {
        return $this->loadAll($conditions)
            ->getFirstItem();
    }

    public function getWhereIn($column, $values)
    {
        return $this->loadAll([
            $column => [
                'value' => $values,
                'operator' => 'in',
            ]
        ]);
    }

    protected function loadAll($conditions)
    {
        if (! is_array($conditions)) {
            $conditions = ['entity_id' => $conditions];
        }

        $collection = $this->instance()
            ->getCollection();

        foreach ($conditions as $attribute => $value) {
            if (!is_array($value)) {
                $value = [
                    'operator' => 'eq',
                    'value' => $value
                ];
            }

            $collection->addAttributeToFilter($attribute, [$value['operator'] => $value['value']]);
        }

        return $collection->load();
    }

    private function instance()
    {
        return ObjectManager::getInstance()
            ->create($this->modelClass);
    }
}
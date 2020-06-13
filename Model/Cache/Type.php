<?php

namespace LaravelEnso\MagentoProductSync\Model\Cache;

use Magento\Framework\App\Cache\Type\FrontendPool;
use Magento\Framework\Cache\Frontend\Decorator\TagScope;

class Type extends TagScope
{
    const TYPE_IDENTIFIER = 'enso_api_cache';
    const CACHE_TAG = 'enso_api_cache';

    const IMPORT_CACHE_KEY = 'import';

    public function __construct(FrontendPool $cacheFrontendPool)
    {
        parent::__construct(
            $cacheFrontendPool->get(static::TYPE_IDENTIFIER),
            static::CACHE_TAG
        );
    }
}
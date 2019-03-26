<?php

namespace Tigren\RedisManager\Model\Config;

class CacheType implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Return array of options as value-label pairs, eg. value => label
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            'session' => 'Session',
            'default' => 'Default',
            'page_cache' => 'Page Cache',
        ];
    }
}
<?php
/**
 * @copyright Copyright (c) 2019 www.tigren.com
 */

namespace Tigren\RedisManager\Model\Config;

use Magento\Framework\Option\ArrayInterface;

class CacheType implements ArrayInterface
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
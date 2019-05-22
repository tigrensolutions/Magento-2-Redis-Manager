<?php
/**
 * @copyright Copyright (c) 2019 www.tigren.com
 */

namespace Tigren\RedisManager\Model;

use Cm_Cache_Backend_Redis;
use Credis_Client;

/**
 * Class Redis
 * @package Tigren\RedisManager\Model
 */
class Redis extends Cm_Cache_Backend_Redis
{
    /**
     * Get redis client
     *
     * @return Credis_Client
     */
    public function getRedis()
    {
        return $this->_redis;
    }
}

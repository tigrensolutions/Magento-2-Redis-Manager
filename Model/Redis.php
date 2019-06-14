<?php
/**
 * @author    Tigren Solutions <info@tigren.com>
 * @copyright Copyright (c) 2019 Tigren Solutions <https://www.tigren.com>. All rights reserved.
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Tigren\RedisManager\Model;

use Cm_Cache_Backend_Redis;
use Credis_Client;

/**
 * Class Redis
 *
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

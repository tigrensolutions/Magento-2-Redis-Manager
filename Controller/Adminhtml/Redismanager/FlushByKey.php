<?php
/*
 * @author    Tigren Solutions <info@tigren.com>
 * @copyright Copyright (c) 2022 Tigren Solutions <https://www.tigren.com>. All rights reserved.
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Tigren\RedisManager\Controller\Adminhtml\Redismanager;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class FlushByKey
 *
 * @package Tigren\RedisManager\Controller\Adminhtml\Redismanager
 */
class FlushByKey extends FlushAbstract
{
    /**
     * @return ResponseInterface|ResultInterface
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\RuntimeException
     */
    public function execute()
    {
        $keys = $this->getRequest()->getPost('redisKeys');
        $clearCount = 0;

        if ($keys) {
            $keys = explode("\n", $keys);
            $keys = array_map([$this, '_prepareKey'], $keys);
            $services = $this->_redisManagerHelper->getServices();
            foreach ($services as $service) {
                $redis = $this->_redisManagerHelper->getRedisInstance($service)->getRedis();
                $matched = [];
                foreach ($keys as $key) {
                    if ($key !== false) {
                        $matched = array_merge($matched, $redis->keys($key));
                    }
                }
                if (count($matched)) {
                    $clearCount += $redis->del($matched);
                }
            }
        }

        $this->messageManager->addSuccess(
            __(
                '%1 key(s) cleared',
                $clearCount
            )
        );

        return $this->_redirect('redismanager/redismanager');
    }

    /**
     * Prepare keys for search
     *
     * @param string $key
     * @return boolean|string
     */
    protected function _prepareKey($key)
    {
        $key = trim($key);
        if (empty($key)) {
            return false;
        }
        return '*' . $key . '*';
    }
}

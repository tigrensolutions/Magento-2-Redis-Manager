<?php

namespace Tigren\RedisManager\Controller\Adminhtml\Redismanager;

/**
 * Class FlushCache
 * @package Tigren\RedisManager\Controller\Adminhtml\Redismanager
 */
class FlushCache extends FlushAbstract
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if (!extension_loaded('redis')) {
            $this->messageManager->addErrorMessage('The Redis extension is not loaded.');
        } else {
            $flushThis = $this->getRequest()->getParams();

            if (!empty($flushThis['database']) && $flushThis['database'] == 0 || $flushThis['database'] != "") {
                try {
                    $redisInstance = $this->_getRedisInstance($flushThis);
                    $redisInstance->flushDB();
                    if ($this->getScopeConfig('redismanager/setting/syncflush')) {
                        $this->runCleanCache();
                    }
                    $this->messageManager->addSuccessMessage('The Redis Cache and Magento Cache have been flushed.');
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage('The Redis cache and Magento Cache were not flushed');
                }
            } else {
                $this->messageManager->addErrorMessage('The Redis cache and Magento Cache were not flushed');
            }
        }

        return $this->_redirect('redismanager/redismanager');
    }
}
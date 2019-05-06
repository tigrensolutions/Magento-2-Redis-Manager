<?php

namespace Tigren\RedisManager\Controller\Adminhtml\Redismanager;

/**
 * Class FlushAll
 * @package Tigren\RedisManager\Controller\Adminhtml\Redismanager
 */
class FlushAll extends FlushAbstract
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if (!extension_loaded('redis')) {
            $this->messageManager->addErrorMessage('The Redis extension is not loaded.');
        } else {
            $session = $this->deploymentConfig->get('session');
            $caches = $this->deploymentConfig->get('cache/frontend');
            $redisInstances = [];

            if (!empty($session['redis'])) {
                $redisInstances[] = $this->_getRedisInstance($session['redis'], true);
            }

            if ($caches['default']['backend'] && $caches['default']['backend'] == 'Cm_Cache_Backend_Redis') {
                $redisInstances[] = $this->_getRedisInstance($caches['default']['backend_options']);
            }

            if ($caches['page_cache']['backend'] && $caches['page_cache']['backend'] == 'Cm_Cache_Backend_Redis') {
                $redisInstances[] = $this->_getRedisInstance($caches['page_cache']['backend_options']);
            }

            if (!empty($redisInstances)) {
                foreach ($redisInstances as $redisInstance) {
                    try {
                        $redisInstance->flushDB();
                    } catch (\Exception $e) {
                        // do something here
                    }
                }
                if ($this->getScopeConfig('redismanager/setting/syncflush')) {
                    $this->runCleanCache();
                }
                $this->messageManager->addSuccessMessage('The Redis Cache and Magento Cache have been flushed.');
            } else {
                $this->messageManager->addSuccessMessage('The Redis Cache and Magento Cache were not flushed.');
            }
        }

        return $this->_redirect('redismanager/redismanager');
    }
}
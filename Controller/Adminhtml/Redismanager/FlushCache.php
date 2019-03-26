<?php

namespace Tigren\RedisManager\Controller\Adminhtml\RedisManager;

use Magento\Backend\App\Action;

/**
 * Class FlushCache
 * @package Tigren\RedisManager\Controller\Adminhtml\RedisManager
 */
class FlushCache extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\App\Cache\Frontend\Pool
     */
    protected $_cacheFrontendPool;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * FlushCache constructor.
     * @param Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool
    ) {
        parent::__construct($context);
        $this->_scopeConfig = $scopeConfig;
        $this->messageManager = $context->getMessageManager();
        $this->_cacheFrontendPool = $cacheFrontendPool;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if ($this->getScopeConfig('redismanager/setting/syncflush')) {
            $flushThis = $this->getRequest()->getParams();
            if (!empty($flushThis['database']) && $flushThis['database'] == 0 || $flushThis['database'] != "") {
                $database = $flushThis['database'];
                $commands = 'redis-cli -n ' . $database . ' flushdb';
                $this->runCleanCache();
                exec($commands, $output, $return);
                if ($output = "OK") {
                    $this->messageManager->addSuccessMessage('The Redis Cache and Magento Cache has been flushed.');
                } else {
                    $this->messageManager->addErrorMessage('The Redis cache and Magento Cache not flushed');
                }
            } else {
                $this->messageManager->addErrorMessage('The Redis cache and Magento Cache not flushed');
            }
            return $this->_redirect('redismanager/redismanager');
        } else {
            $flushThis = $this->getRequest()->getParams();
            if (!empty($flushThis['database']) && $flushThis['database'] == 0 || $flushThis['database'] != "") {
                $database = $flushThis['database'];
                $commands = 'redis-cli -n ' . $database . ' flushdb';
                exec($commands, $output, $return);
                if ($output = "OK") {
                    $this->messageManager->addSuccessMessage('The Redis cache has been flushed.');
                } else {
                    $this->messageManager->addErrorMessage('The Redis cache not flushed');
                }
            } else {
                $this->messageManager->addErrorMessage('The Redis cache not flushed');
            }
            return $this->_redirect('redismanager/redismanager');
        }
    }

    /**
     * @param $path
     * @return mixed
     */
    public function getScopeConfig($path)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return bool
     */
    public function runCleanCache()
    {
        foreach ($this->_cacheFrontendPool as $cacheFrontend) {
            $cacheFrontend->getBackend()->clean();
            $cacheFrontend->clean();
        }
        return true;
    }
}
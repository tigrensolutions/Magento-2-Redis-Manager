<?php

namespace Tigren\RedisManager\Controller\Adminhtml\RedisManager;

use Magento\Backend\App\Action;

/**
 * Class FlushAll
 * @package Tigren\RedisManager\Controller\Adminhtml\RedisManager
 */
class FlushAll extends \Magento\Backend\App\Action
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
     * FlushAll constructor.
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
            if (!empty($flushThis['port']) && $flushThis['port'] == 0 || $flushThis['port'] != "") {
                $port = $flushThis['port'];
                $commands = 'redis-cli -p ' . $port . ' flushall';
                $this->runCleanCache();
                exec($commands, $output, $return);
                if ($output = "OK") {
                    $this->messageManager->addSuccessMessage('The Redis cache and Magento Cache has been flushed all.');
                } else {
                    $this->messageManager->addErrorMessage('The Redis cache and Magento Cache not flushed all');
                }
            } else {
                $commands = 'redis-cli FLUSHALL';
                $this->runCleanCache();
                exec($commands, $output, $return);
                if ($output = "OK") {
                    $this->messageManager->addSuccessMessage('The Redis cache and Magento Cache has been flushed all.');
                } else {
                    $this->messageManager->addErrorMessage('The Redis cache and Magento Cache not flushed all');
                }
            }
            return $this->_redirect('redismanager/redismanager');
        } else {
            $flushThis = $this->getRequest()->getParams();
            if (!empty($flushThis['port']) && $flushThis['port'] == 0 || $flushThis['port'] != "") {
                $port = $flushThis['port'];
                $commands = 'redis-cli -p ' . $port . ' flushall';
                exec($commands, $output, $return);
                if ($output = "OK") {
                    $this->messageManager->addSuccessMessage('The Redis cache has been flushed all.');
                } else {
                    $this->messageManager->addErrorMessage('The Redis cache not flushed all');
                }
            } else {
                $commands = 'redis-cli FLUSHALL';
                exec($commands, $output, $return);
                if ($output = "OK") {
                    $this->messageManager->addSuccessMessage('The Redis cache has been flushed all.');
                } else {
                    $this->messageManager->addErrorMessage('The Redis cache not flushed all');
                }
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
     * @return mixed
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
<?php

namespace Tigren\RedisManager\Controller\Adminhtml\Redismanager;

use Magento\Backend\App\Action;

/**
 * Class FlushAbstract
 * @package Tigren\RedisManager\Controller\Adminhtml\Redismanager
 */
abstract class FlushAbstract extends Action
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
     * @var \Magento\Framework\App\DeploymentConfig
     */
    protected $deploymentConfig;

    /**
     * FlushAll constructor.
     * @param Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool
     * @param \Magento\Framework\App\DeploymentConfig $deploymentConfig
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool,
        \Magento\Framework\App\DeploymentConfig $deploymentConfig
    ) {
        parent::__construct($context);
        $this->_scopeConfig = $scopeConfig;
        $this->messageManager = $context->getMessageManager();
        $this->_cacheFrontendPool = $cacheFrontendPool;
        $this->deploymentConfig = $deploymentConfig;
    }

    /**
     * @param $path
     * @return mixed
     */
    public function getScopeConfig($path)
    {
        return (boolean) $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
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

    /**
     * @param $redisOptions
     * @param bool $isSession
     * @return \Redis
     */
    protected function _getRedisInstance($redisOptions, $isSession = false)
    {
        $redisInstance = new \Redis();

        if ($isSession) {
            $default = [
                'server' => '127.0.0.1',
                'port' => 6379,
                'database' => '',
                'password' => '',
                'timeout' => '2.5'
            ];
            $redisOptions['server'] = !empty($redisOptions['host']) ? $redisOptions['host'] : '';
            $config = array_merge($default, $redisOptions);
        } else {
            $default = [
                'server' => '127.0.0.1',
                'port' => 6379,
                'database' => '',
                'password' => '',
                'timeout' => '2.5'
            ];
            $config = array_merge($default, $redisOptions);
        }

        $redisInstance->connect($config['server'], $config['port'], $config['timeout']);
        $redisInstance->select($config['database']);

        return $redisInstance;
    }
}
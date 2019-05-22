<?php
/**
 * @copyright Copyright (c) 2019 www.tigren.com
 */

namespace Tigren\RedisManager\Helper;

use Cm_Cache_Backend_Redis;
use Exception;
use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Tigren\RedisManager\Model\Redis;
use Zend_Cache;
use Zend_Cache_Exception;

/**
 * Class Data
 * @package Tigren\RedisManager\Helper
 */
class Data extends AbstractHelper
{
    const DEFAULT_MISSING_STRING = 'N/A';

    const XML_PATH_AUTO_DETECT_REDIS_SERVICES = 'redismanager/setting/auto_detect';

    const XML_PATH_SYNC_FLUSH = 'redismanager/setting/syncflush';

    const XML_PATH_MANUAL_CONFIG = 'redismanager/setting/manual_config';

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var Pool
     */
    protected $_cacheFrontendPool;

    /**
     * @var TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var DeploymentConfig
     */
    protected $deploymentConfig;

    /**
     * Json Serializer
     *
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * Services cache
     *
     * @var array
     */
    protected $_services;

    /**
     * Cached array of info from a redis instance
     *
     * @var array
     */
    protected $_info;

    /**
     * Data constructor.
     * @param Context $context
     * @param TimezoneInterface $localeDate
     * @param StoreManagerInterface $storeManager
     * @param Pool $cacheFrontendPool
     * @param DeploymentConfig $deploymentConfig
     * @param Json|null $serializer
     */
    public function __construct(
        Context $context,
        TimezoneInterface $localeDate,
        StoreManagerInterface $storeManager,
        Pool $cacheFrontendPool,
        DeploymentConfig $deploymentConfig,
        Json $serializer = null
    ) {
        $this->_storeManager = $storeManager;
        $this->_localeDate = $localeDate;
        $this->_cacheFrontendPool = $cacheFrontendPool;
        $this->deploymentConfig = $deploymentConfig;
        $this->serializer = $serializer ?: ObjectManager::getInstance()
            ->get(Json::class);
        parent::__construct($context);
    }

    /**
     * Perform a flushAll when sync flush is enabled (for use in the event observers)
     *
     * @return void
     */
    public function flushAllByObserver()
    {
        if ($this->getSyncFlush()) {
            $this->flushAll();
        }
    }

    /**
     * @return bool
     */
    public function getSyncFlush()
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_SYNC_FLUSH,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Flush all Redis caches
     *
     * @param string $flushThis
     * @return array
     */
    public function flushAll($flushThis = null)
    {
        $flushed = [];

        foreach ($this->getServices() as $service) {
            $serviceMatch = $service['server'] . ':' . $service['port'];
            if (in_array($serviceMatch, $flushed)
                || (!is_null($flushThis) && $flushThis != $serviceMatch)
            ) {
                continue;
            }
            try {
                $this->getRedisInstance($service)->getRedis()->flushAll();
                $flushed[] = $serviceMatch;
                $serviceName = $service['name'] . ' (' . $service['server'] . ':' . $service['port'] . ')';
            } catch (Exception $e) {
                // do something here
            }
        }

        return $flushed;
    }

    /**
     * Fetch all redis services
     *
     * @return array
     */
    public function getServices()
    {
        if (!isset($this->_services)) {
            if ($this->getAutoDetectRedisServices()) {
                $this->_services = [];
                $session = $this->deploymentConfig->get('session');
                $caches = $this->deploymentConfig->get('cache/frontend');

                if (!empty($session['redis'])) {
                    $this->_services[] = $this->_processRedisOptions(__('Session'), $session['redis'], true);
                }

                if ($caches['default']['backend'] && $caches['default']['backend'] == 'Cm_Cache_Backend_Redis') {
                    $this->_services[] = $this->_processRedisOptions(
                        __('Cache'),
                        $caches['default']['backend_options']
                    );
                }

                if ($caches['page_cache']['backend'] && $caches['page_cache']['backend'] == 'Cm_Cache_Backend_Redis') {
                    $this->_services[] = $this->_processRedisOptions(
                        __('Page Cache'),
                        $caches['page_cache']['backend_options']
                    );
                }
            } else {
                $this->_services = $this->getManualConfig();
            }
        }

        return $this->_services;
    }

    /**
     * @return bool
     */
    public function getAutoDetectRedisServices()
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_AUTO_DETECT_REDIS_SERVICES,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param $name
     * @param $redisOptions
     * @param bool $isSession
     * @return Cm_Cache_Backend_Redis
     */
    protected function _processRedisOptions($name, $redisOptions, $isSession = false)
    {
        if ($isSession) {
            $default = [
                'server' => '127.0.0.1',
                'port' => 6379,
                'database' => '',
                'password' => '',
                'timeout' => '2.5'
            ];
            $redisOptions['server'] = !empty($redisOptions['host']) ? $redisOptions['host'] : '';
            $redisOptions = array_merge($default, $redisOptions);
        } else {
            $default = [
                'server' => '127.0.0.1',
                'port' => 6379,
                'database' => '',
                'password' => '',
                'timeout' => '2.5'
            ];
            $redisOptions = array_merge($default, $redisOptions);
        }

        $redisOptions['name'] = $name;

        return $redisOptions;
    }

    /**
     * @return array
     */
    public function getManualConfig()
    {
        $manualConfig = $this->scopeConfig->getValue(
            self::XML_PATH_MANUAL_CONFIG,
            ScopeInterface::SCOPE_STORE
        );

        if ($manualConfig) {
            return $this->serializer->unserialize($manualConfig);
        } else {
            return [];
        }
    }

    /**
     * @param $redisOptions
     * @return Redis
     */
    public function getRedisInstance($redisOptions)
    {
        $redisInstance = new Redis($redisOptions);
        return $redisInstance;
    }

    /**
     * @return array
     */
    public function getSortedAllServices()
    {
        $sortedAllServices = [];

        foreach ($this->getServices() as $key => $service) {
            $hostPort = $service['server'] . ':' . $service['port'];
            if (!isset($sortedAllServices[$hostPort])) {
                $client = $this->getRedisInstance($service);
                $sortedAllServices[$hostPort] = $this->_getSortedService($service, $key, $client);
                continue;
            }
            $client = $this->getRedisInstance($service);
            $sortedAllServices[$hostPort]['services'][$key] = [
                'name' => $service['name'],
                'database' => $service['database'],
                'keys' => count($client->getRedis()->keys('*'))
            ];
        }

        return $sortedAllServices;
    }

    /**
     * Get a formatted array of data from the redis info
     *
     * @param array $service
     * @param $id
     * @param $client
     * @return array
     */
    protected function _getSortedService(array $service, $id, $client)
    {
        $this->_info = $client->getRedis()->info();
        return [
            'server' => $service['server'],
            'port' => $service['port'],
            'uptime' => $this->_getUptime(),
            'connections' => $this->_getInfo('connected_clients'),
            'memory' => $this->_getMemory(),
            'role' => $this->_getInfo('role') . $this->_getSlaves(),
            'lastsave' => $this->_getLastSave(),
            'services' => [
                $id => [
                    'name' => $service['name'],
                    'database' => $service['database'],
                    'keys' => count($client->getRedis()->keys('*'))
                ]
            ]
        ];
    }

    /**
     * Get the uptime for this service
     *
     * @return string
     */
    protected function _getUptime()
    {
        $uptime = $this->_getInfo('uptime_in_seconds', false);
        if (!$uptime) {
            return __(self::DEFAULT_MISSING_STRING);
        }
        return __(
            '%1 days, %2 hours, %3 minutes, 4s seconds',
            floor($uptime / 86400),
            floor($uptime / 3600) % 24,
            floor($uptime / 60) % 60,
            floor($uptime % 60)
        );
    }

    /**
     * Get information from the redis client
     *
     * @param string $key
     * @param mixed $ifMissing
     *
     * @return mixed
     */
    protected function _getInfo($key, $ifMissing = self::DEFAULT_MISSING_STRING)
    {
        if (isset($this->_info[$key])) {
            return $this->_info[$key];
        }
        return is_string($ifMissing) ? __($ifMissing) : $ifMissing;
    }

    /**
     * Get the memory usage
     *
     * @return string
     */
    protected function _getMemory()
    {
        $used = $this->_getInfo('used_memory_human', false);
        $peak = $this->_getInfo('used_memory_peak_human', false);
        if (!$used || !$peak) {
            return __(self::DEFAULT_MISSING_STRING);
        }
        return $used . ' / ' . $peak;
    }

    /**
     * Get any connected slaves
     *
     * @return string
     */
    protected function _getSlaves()
    {
        $slaves = $this->_getInfo('connected_slaves', false);
        if (!$slaves) {
            return '';
        }
        return __(' (%s slaves)', $slaves);
    }

    /**
     * Get the last save timestamp
     *
     * @return string
     */
    protected function _getLastSave()
    {
        $lastSave = $this->_getInfo('rdb_last_save_time', false);
        if (!$lastSave) {
            return __(self::DEFAULT_MISSING_STRING);
        }

        return date('Y-m-d H:i:s', $lastSave);
    }

    /**
     * Flush a db
     *
     * @param array $service
     * @return void
     * @throws Zend_Cache_Exception
     */
    public function flushDb(array $service)
    {
        $redis = $this->getRedisInstance($service);
        $redis->clean(Zend_Cache::CLEANING_MODE_ALL);
    }

    /**
     * @return mixed
     */
    protected function _runCleanCache()
    {
        foreach ($this->_cacheFrontendPool as $cacheFrontend) {
            $cacheFrontend->getBackend()->clean();
            $cacheFrontend->clean();
        }
        return true;
    }
}

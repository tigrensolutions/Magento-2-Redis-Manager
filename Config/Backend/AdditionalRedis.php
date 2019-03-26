<?php

namespace Tigren\RedisManager\Config\Backend;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\App\DeploymentConfig\Writer\PhpFormatter;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Filesystem\Directory\WriteFactory;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Class AdditionalRedis
 */
class AdditionalRedis extends Value
{
    /**
     * @var \Magento\Framework\App\DeploymentConfig
     */
    protected $deploymentConfig;

    /**
     * Json Serializer
     *
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var WriteInterface
     */
    private $write;

    /**
     * AdditionalRedis constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param DirectoryList $directoryList
     * @param WriteFactory $writeFactory
     * @param array $data
     * @param Json|null $serializer
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        DirectoryList $directoryList,
        WriteFactory $writeFactory,
        array $data = [],
        Json $serializer = null
    )
    {
        $this->directoryList = $directoryList;
        $this->write = $writeFactory->create(BP);
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(Json::class);
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Prepare data before save
     *
     * @return AdditionalRedis
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Exception
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        if (!is_array($value)) {
            try {
                $value = $this->serializer->unserialize($value);
            } catch (\InvalidArgumentException $e) {
                $value = [];
            }
        } else {
            unset($value['__empty']);
        }
        $this->setValue($this->serializer->serialize($value));

        $envPath = $this->getEnvPath();
        if ($this->write->isReadable($this->write->getRelativePath($envPath))) {
            $envData = include $envPath;

            $hasDefaultCacheData = false;
            $hasPageCacheData = false;
            $hasSessionData = false;

            if (!empty($value) && is_array($value)) {
                foreach ($value as $cacheData) {
                    
                    if (isset($cacheData['cache_type']) && isset($cacheData['server'])
                        && isset($cacheData['database']) && isset($cacheData['port'])) {
                        switch ($cacheData['cache_type']) {
                            case 'default':
                                $defaultCacheData = [
                                    'backend' => 'Cm_Cache_Backend_Redis',
                                    'backend_options' => [
                                        'server' => $cacheData['server'],
                                        'port' => $cacheData['port'],
                                        'database' => $cacheData['database'],
                                        'password' => !empty($cacheData['password']) ? $cacheData['password'] : ''
                                    ]
                                ];
                                $envData['cache']['frontend']['default'] = $defaultCacheData;
                                $hasDefaultCacheData = true;
                                break;

                            case 'page_cache':
                                $pageCacheData = [
                                    'backend' => 'Cm_Cache_Backend_Redis',
                                    'backend_options' => [
                                        'server' => $cacheData['server'],
                                        'port' => $cacheData['port'],
                                        'database' => $cacheData['database'],
                                        'password' => !empty($cacheData['password']) ? $cacheData['password'] : ''
                                    ]
                                ];
                                $envData['cache']['frontend']['page_cache'] = $pageCacheData;
                                $hasPageCacheData = true;
                                break;

                            case 'session':
                                $sessionData = [
                                    'save' => 'redis',
                                    'redis' => [
                                        'host' => $cacheData['server'],
                                        'port' => $cacheData['port'],
                                        'password' => !empty($cacheData['password']) ? $cacheData['password'] : '',
                                        'timeout' => '2.5',
                                        'persistent_identifier' => '',
                                        'database' => $cacheData['database'],
                                        'compression_threshold' => '2048',
                                        'compression_library' => 'gzip',
                                        'log_level' => '3',
                                        'max_concurrency' => '6',
                                        'break_after_frontend' => '5',
                                        'break_after_adminhtml' => '30',
                                        'first_lifetime' => '600',
                                        'bot_first_lifetime' => '60',
                                        'bot_lifetime' => '7200',
                                        'disable_locking' => '0',
                                        'min_lifetime' => '60',
                                        'max_lifetime' => '2592000'
                                    ]
                                ];
                                $envData['session'] = $sessionData;
                                $hasSessionData = true;
                                break;

                            default:
                                // do nothing
                        }
                    }
                }
            }

            if (!$hasDefaultCacheData && isset($envData['cache']['frontend']['default'])) {
                unset($envData['cache']['frontend']['default']);
            }

            if (!$hasPageCacheData && isset($envData['cache']['frontend']['page_cache'])) {
                unset($envData['cache']['frontend']['default']);
            }

            if (!$hasSessionData) {
                $envData['session'] = ['save' => 'files'];
            }

            $formatter = new PhpFormatter();
            $contents = $formatter->format($envData);

            $this->write->writeFile($this->write->getRelativePath($envPath), $contents);
        }

        return $this;
    }

    /**
     * Returns path to env.php file
     *
     * @return string
     * @throws \Exception
     */
    private function getEnvPath()
    {
        $deploymentConfig = $this->directoryList->getPath(DirectoryList::CONFIG);
        $configPool = new ConfigFilePool();
        $envPath = $deploymentConfig . '/' . $configPool->getPath(ConfigFilePool::APP_ENV);
        return $envPath;
    }

    /**
     * Process data after load
     *
     * @return $this
     */
    public function afterLoad()
    {
        if ($this->getValue()) {
            $value = $this->serializer->unserialize($this->getValue());
            if (is_array($value)) {
                unset($value['__empty']);
                $this->setValue($value);
            }
        }
        return $this;
    }
}

<?php

namespace Tigren\RedisManager\Block\Adminhtml;

use Magento\Framework\View\Element\Template;

/**
 * Class Grid
 * @package Tigren\RedisManager\Block\Adminhtml
 */
class Grid extends Template
{
    /**
     * @var \Magento\Framework\App\DeploymentConfig
     */
    protected $deploymentConfig;

    /**
     * Grid constructor.
     * @param Template\Context $context
     * @param \Magento\Framework\App\DeploymentConfig $deploymentConfig
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Framework\App\DeploymentConfig $deploymentConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->deploymentConfig = $deploymentConfig;
    }

    /**
     * @return mixed|null
     */
    public function getSessionData()
    {
        $session = $this->deploymentConfig->get('session');
        return $session;
    }

    /**
     * @return mixed|null
     */
    public function getCacheData()
    {
        $cache = $this->deploymentConfig->get('cache/frontend');
        return $cache;
    }
}
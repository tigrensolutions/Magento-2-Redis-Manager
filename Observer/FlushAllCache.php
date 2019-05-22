<?php
/**
 * @copyright Copyright (c) 2019 www.tigren.com
 */

namespace Tigren\RedisManager\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Tigren\RedisManager\Helper\Data;

/**
 * Class FlushAllCache
 * @package Tigren\RedisManager\Observer
 */
class FlushAllCache implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $_redisManagerHelper;

    /**
     * @param Data $redisManagerHelper
     */
    public function __construct(
        Data $redisManagerHelper
    ) {
        $this->_redisManagerHelper = $redisManagerHelper;
    }

    /**
     * Flush all redis databases
     * @param Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Observer $observer)
    {
        $this->_redisManagerHelper->flushAllByObserver();
    }
}

<?php
/**
 * @author    Tigren Solutions <info@tigren.com>
 * @copyright Copyright (c) 2019 Tigren Solutions <https://www.tigren.com>. All rights reserved.
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Tigren\RedisManager\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Tigren\RedisManager\Helper\Data;

/**
 * Class FlushAllCache
 *
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
     *
     * @param Observer $observer
     * @return                                        void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Observer $observer)
    {
        $this->_redisManagerHelper->flushAllByObserver();
    }
}

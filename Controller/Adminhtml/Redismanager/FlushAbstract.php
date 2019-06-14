<?php
/**
 * @author    Tigren Solutions <info@tigren.com>
 * @copyright Copyright (c) 2019 Tigren Solutions <https://www.tigren.com>. All rights reserved.
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Tigren\RedisManager\Controller\Adminhtml\Redismanager;

use Magento\Backend\App\Action;
use Magento\Framework\Message\ManagerInterface;
use Tigren\RedisManager\Helper\Data;

/**
 * Class FlushAbstract
 *
 * @package Tigren\RedisManager\Controller\Adminhtml\Redismanager
 */
abstract class FlushAbstract extends Action
{
    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var Data
     */
    protected $_redisManagerHelper;

    /**
     * FlushAll constructor.
     *
     * @param Action\Context $context
     * @param Data $redisManagerHelper
     */
    public function __construct(
        Action\Context $context,
        Data $redisManagerHelper
    ) {
        parent::__construct($context);
        $this->messageManager = $context->getMessageManager();
        $this->_redisManagerHelper = $redisManagerHelper;
    }
}

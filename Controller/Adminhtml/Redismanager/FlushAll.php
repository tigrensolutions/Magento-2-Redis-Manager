<?php
/**
 * @copyright Copyright (c) 2019 www.tigren.com
 */

namespace Tigren\RedisManager\Controller\Adminhtml\Redismanager;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class FlushAll
 * @package Tigren\RedisManager\Controller\Adminhtml\Redismanager
 */
class FlushAll extends FlushAbstract
{
    /**
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $flushThis = $this->getRequest()->getParam('server', null);
        $flushAllResult = $this->_redisManagerHelper->flushAll($flushThis);

        if (is_array($flushAllResult)) {
            $this->messageManager->addSuccessMessage('The Redis Services have been flushed.');
        } else {
            $this->messageManager->addSuccessMessage('The Redis Services were not flushed.');
        }

        return $this->_redirect('redismanager/redismanager');
    }
}

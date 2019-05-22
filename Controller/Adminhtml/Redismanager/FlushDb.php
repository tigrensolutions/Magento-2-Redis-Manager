<?php
/**
 * @copyright Copyright (c) 2019 www.tigren.com
 */

namespace Tigren\RedisManager\Controller\Adminhtml\Redismanager;

use Exception;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class FlushDb
 * @package Tigren\RedisManager\Controller\Adminhtml\Redismanager
 */
class FlushDb extends FlushAbstract
{
    /**
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $services = $this->_redisManagerHelper->getServices();
        if ($id === false || !isset($services[$id])) {
            $this->messageManager->addErrorMessage('The requested service was not found');
        } else {
            try {
                $this->_redisManagerHelper->flushDb($services[$id]);
                $this->messageManager->addSuccessMessage('The Redis Service has been flushed.');
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage('The Redis Service was not flushed');
            }
        }

        return $this->_redirect('redismanager/redismanager');
    }
}
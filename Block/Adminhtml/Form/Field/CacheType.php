<?php
namespace Tigren\RedisManager\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Html\Select;
use Magento\Framework\View\Element\Context;

/**
 * Class CacheType
 * @package Tigren\RedisManager\Block\Adminhtml\Form\Field
 */
class CacheType extends Select
{
    /**
     * @var \Tigren\RedisManager\Model\Config\CacheType
     */
    protected $cacheType;

    /**
     * CacheType constructor.
     * @param \Tigren\RedisManager\Model\Config\CacheType $cacheType
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        \Tigren\RedisManager\Model\Config\CacheType $cacheType,
        Context $context,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->cacheType = $cacheType;
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->cacheType->toOptionArray());
        }
        return parent::_toHtml();
    }

    /**
     * Sets name for input element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }
}
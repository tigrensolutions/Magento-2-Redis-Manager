<?php

namespace Tigren\RedisManager\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;

/**
 * Class ManualConfiguration
 * @package Tigren\RedisManager\Block\Adminhtml\Form\Field
 */
class ManualConfiguration extends AbstractFieldArray
{
    /**
     * @var CacheType
     */
    protected $cacheType = null;

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'cache_type',
            [
                'label' => __('Cache Type'),
                'style' => 'width:150px',
                'unique' => true,
                'renderer' => $this->getCacheTypeRenderer(),
            ]
        );
        $this->addColumn(
            'server',
            [
                'label' => __('Host'),
                'style' => 'width:100px',
                'class' => 'required-entry',
            ]
        );
        $this->addColumn(
            'database',
            [
                'label' => __('Database'),
                'style' => 'width:100px',
                'class' => 'required-entry'
            ]
        );
        $this->addColumn(
            'port',
            [
                'label' => __('Port'),
                'style' => 'width:100px',
                'class' => 'required-entry',
            ]
        );
        $this->addColumn(
            'password',
            [
                'label' => __('Password'),
                'style' => 'width:100px'
            ]
        );

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * @return CacheType
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getCacheTypeRenderer()
    {
        if (!$this->cacheType) {
            $this->cacheType = $this->getLayout()->createBlock(
                CacheType::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->cacheType;
    }

    /**
     * Prepare existing row data object
     *
     * @param DataObject $row
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareArrayRow(DataObject $row)
    {
        $cacheType = $row->getCacheType();
        $options = [];
        if ($cacheType) {
            $options['option_' . $this->getCacheTypeRenderer()->calcOptionHash($cacheType)]
                = 'selected="selected"';
        }
        $row->setData('option_extra_attrs', $options);
    }
}
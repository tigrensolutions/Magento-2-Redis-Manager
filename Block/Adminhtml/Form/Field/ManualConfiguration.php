<?php
/**
 * @author    Tigren Solutions <info@tigren.com>
 * @copyright Copyright (c) 2019 Tigren Solutions <https://www.tigren.com>. All rights reserved.
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Tigren\RedisManager\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

/**
 * Class ManualConfiguration
 *
 * @package Tigren\RedisManager\Block\Adminhtml\Form\Field
 */
class ManualConfiguration extends AbstractFieldArray
{
    protected function _prepareToRender()
    {
        $this->addColumn(
            'name',
            [
                'label' => __('Name'),
                'style' => 'width:100px',
                'class' => 'required-entry',
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

        $this->addColumn(
            'database',
            [
                'label' => __('Database'),
                'style' => 'width:100px',
                'class' => 'required-entry'
            ]
        );

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }
}

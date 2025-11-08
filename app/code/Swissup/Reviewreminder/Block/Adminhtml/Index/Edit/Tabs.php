<?php
namespace Swissup\Reviewreminder\Block\Adminhtml\Index\Edit;

/**
 * Admin page left menu
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('index_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Reminder Information'));
    }
    /**
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareLayout()
    {
        $this->addTab(
            'main',
            [
                'label' => __('Reminder Information'),
                'content' => $this->getLayout()->createBlock('Swissup\Reviewreminder\Block\Adminhtml\Index\Edit\Tab\Main')->toHtml(),
                'active' => true
            ]
        );

        $this->addTab(
            'products',
            [
                'label' => __('Order Products'),
                'url' => $this->getUrl('reviewreminder/*/products', ['_current' => true]),
                'class' => 'ajax',
                'group_code' => 'products'
            ]
        );

        $this->addTab(
            'preview',
            [
                'label' => __('Email Preview'),
                'content' => $this->getLayout()->createBlock('Swissup\Reviewreminder\Block\Adminhtml\Index\Edit\Tab\Preview')->toHtml()
            ]
        );

        return $this;
    }
}

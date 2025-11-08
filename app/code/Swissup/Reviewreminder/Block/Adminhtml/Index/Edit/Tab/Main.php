<?php
namespace Swissup\Reviewreminder\Block\Adminhtml\Index\Edit\Tab;

/**
 * Reminder edit form main tab
 */
class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Prepare form
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        /* @var $model \Swissup\Reviewreminder\Model\Entity */
        $model = $this->_coreRegistry->registry('reminder');

        /*
         * Checking if user have permissions to save information
         */
        if ($this->_isAllowedAction('Swissup_Reviewreminder::save')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('entity_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Reminder Information')]);

        if ($model->getEntityId()) {
            $fieldset->addField('entity_id', 'hidden', ['name' => 'entity_id']);
        }

        $fieldset->addField(
            'increment_id',
            'label',
            [
                'name' => 'increment_id',
                'label' => __('Order #'),
                'title' => __('Order #'),
                'after_element_html' => ' (<a href="' .
                    $this->getUrl(
                        'sales/order/view',
                        ['order_id' => $model->getOrderId()]
                    ) . '" onclick="this.target=\'blank\'">' .
                    __('Go to Order') . '</a>)'
            ]
        );

        $fieldset->addField(
            'customer_name',
            'label',
            [
                'name' => 'customer_name',
                'label' => __('Customer Name'),
                'title' => __('Customer Name')
            ]
        );

        $fieldset->addField(
            'customer_email',
            'label',
            [
                'name' => 'customer_email',
                'label' => __('Customer Email'),
                'title' => __('Customer Email')
            ]
        );

        $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
        $fieldset->addField(
            'order_date',
            'label',
            [
                'name' => 'order_date',
                'label' => __('Order Date'),
                'title' => __('Order Date'),
                'date_format' => $dateFormat
            ]
        );

        $fieldset->addField(
            'products',
            'label',
            [
                'name' => 'products',
                'label' => __('Products'),
                'title' => __('Products')
            ]
        );

        $fieldset->addField(
            'status',
            'select',
            [
                'label' => __('Status'),
                'title' => __('Status'),
                'name' => 'status',
                'required' => true,
                'options' => $model->getAvailableStatuses(),
                'disabled' => $isElementDisabled
            ]
        );

        $this->_eventManager->dispatch('adminhtml_reviewreminder_index_edit_tab_main_prepare_form', ['form' => $form]);

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Reminder Information');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Reminder Information');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}

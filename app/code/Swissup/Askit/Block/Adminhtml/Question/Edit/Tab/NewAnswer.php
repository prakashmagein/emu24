<?php

namespace Swissup\Askit\Block\Adminhtml\Question\Edit\Tab;

class NewAnswer extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Prepare form
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        /*
         * Checking if user have permissions to save information
         */
        if ($this->_isAllowedAction('Swissup_Askit::message_save')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
            'data' => [
                'id' => 'new_answer_tab_edit_form',
                'action' => $this->getUrl('*/answer/save'),
                'method' => 'post'
                ]
            ]
        );

        $form->setHtmlIdPrefix('answer_');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Add New Answer'), 'class' => 'fieldset-wide']
        );

        $field = $fieldset->addField(
            'answer',
            'textarea',
            [
                'name' => 'answer',
                'label' => __('Text'),
                'title' => __('Text'),
                'style' => 'height:8em',
                'disabled' => $isElementDisabled,
                'state' => 'html',
                'data-form-part' => 'askit_question_form'
            ]
        );

        $this->setForm($form);
        return $this;
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

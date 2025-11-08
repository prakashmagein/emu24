<?php

namespace Swissup\Askit\Block\Question;

use Swissup\Askit\Api\Data\MessageInterface;

class Form extends AbstractForm
{
    /**
     * {@inheritdoc}
     */
    protected $formId = 'swissup_askit_new_question_form';

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();

        if (!$this->jsLayout) {
            $captchaConfig = $this->getCaptchaConfig();
            $this->jsLayout = [
                'components' => [
                    'askitQuestionForm_data_source' => [
                        'component' => 'Magento_Ui/js/form/provider',
                        'submit_url' => $this->getAction(),
                        'config' => [
                            'data' => [
                                'item_type_id' => $this->getItemTypeId(),
                                'item_id' => $this->getItemId()
                            ]
                        ]
                    ],
                    'askitQuestionForm' => [
                        'component' => 'Swissup_Askit/js/view/form',
                        'provider' => 'askitQuestionForm_data_source',
                        'namespace' => 'askitQuestionForm',
                        'template' => 'Swissup_Askit/question/form',
                        'children' => [
                            'askit-messages' => [
                                'component' => 'Swissup_Askit/js/view/messages',
                                'displayArea' => 'askit-messages'
                            ]
                        ]
                        + (empty($captchaConfig) ? [] : [
                            'captcha' => $captchaConfig
                        ])
                    ]
                ]
            ];
        }
    }

    /**
     * Check if current page is suitable for questions form,
     *
     * @return boolean
     */
    public function isSuitablePageType()
    {
        $type = $this->getItemTypeId();
        $types = [MessageInterface::TYPE_CATALOG_PRODUCT,
            MessageInterface::TYPE_CATALOG_CATEGORY,
            MessageInterface::TYPE_CMS_PAGE
        ];
        if (!in_array($type, $types)) {
            return false;
        }

        return true;
    }
}

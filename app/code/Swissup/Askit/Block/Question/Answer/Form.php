<?php

namespace Swissup\Askit\Block\Question\Answer;

use Swissup\Askit\Api\Data\MessageInterface;

class Form extends \Swissup\Askit\Block\Question\AbstractForm
{
    /**
     * @var string
     */
    protected $formId = 'swissup_askit_new_answer_form';

    /**
     * @var MessageInterface
     */
    protected $question;

    /**
     * {@inheritdoc}
     */
    public function getJsLayout()
    {
        $captchaConfig = $this->getCaptchaConfig();
        $scope = $this->getJsScope();
        $this->jsLayout = [
            'components' => [
                "{$scope}_data_source" => [
                    'component' => 'Magento_Ui/js/form/provider',
                    'submit_url' => $this->getNewAnswerAction(),
                    'config' => [
                        'data' => [
                            'parent_id' => $this->getQuestion()->getId()
                        ]
                    ]
                ],
                "{$scope}" => [
                    'component' => 'Swissup_Askit/js/view/form',
                    'provider' => "{$scope}_data_source",
                    'namespace' => "{$scope}",
                    'template' => 'Swissup_Askit/answer/form',
                    'isFormVisible' => true,
                    'isLoggedIn' => $this->getCustomerSession()->isLoggedIn(),
                    'parentMessageId' => $this->getQuestion()->getId(),
                    'gravatar' => $this->getGravatarHtml(),
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

        return parent::getJsLayout();
    }

    /**
     * @param MessageInterface $question
     */
    public function setQuestion($question)
    {
        $this->question = $question;
        return $this;
    }

    /**
     * @return MessageInterface
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * @return string
     */
    public function getNewAnswerAction()
    {
        return $this->getUrl('askit/answer/save');
    }

    /**
     * @return string
     */
    public function getJsScope()
    {
        $question = $this->getQuestion();
        $questionId = is_object($question) ? $question->getId() : '';

        return "askitAnswerForm{$this->getQuestion()->getId()}";
    }

    /**
     * @return string
     */
    public function getGravatarHtml()
    {
        if (!$this->getConfigHelper()->isEnabledGravatar()) {
            return '';
        }

        $customer = $this->getCustomerSession()->getCustomer();
        if (!$customer->getEmail()) {
            return '';
        }

        return $this->getUrlHelper()->getGravatar($customer->getEmail(), 100);
    }
}

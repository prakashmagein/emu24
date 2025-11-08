<?php

namespace Swissup\GdprAskit\Observer;

class RegisterPersonalDataForm implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $observer->getCollection()->addItem(
            new \Swissup\Gdpr\Model\PersonalDataForm([
                'id'     => 'swissup:askit_question',
                'name'   => 'Swissup: Askit Question',
                'action' => 'askit_question_save',
                'js_config' => [
                    'form' => '.askit-question-form form',
                ]
            ])
        );
        $observer->getCollection()->addItem(
            new \Swissup\Gdpr\Model\PersonalDataForm([
                'id'     => 'swissup:askit_answer',
                'name'   => 'Swissup: Askit Answer',
                'action' => 'askit_answer_save',
                'js_config' => [
                    'form' => '.askit-answer-form form',
                ]
            ])
        );
    }
}

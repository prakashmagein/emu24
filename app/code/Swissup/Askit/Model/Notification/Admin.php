<?php

namespace Swissup\Askit\Model\Notification;

class Admin extends NotificationAbstract
{
    const ASKIT_ADMIN_NOTIFICATION_EMAIL = 'askit/email/admin_email';
    /**
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $request = $observer->getEvent()->getRequest();
        $message = $observer->getEvent()->getMessage();
        $question = clone $message;

        $store = $this->storeManager->getStore($question->getStoreId());
        $area = \Magento\Framework\App\Area::AREA_ADMINHTML;

        $this->appEmulation->startEnvironmentEmulation($store->getId(), $area);

        $from = $this->scopeConfig->getValue(
            self::ASKIT_EMAIL_IDENTITY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store->getCode()
        );
        // $to = ['email' => $question->getEmail(), 'name' => $question->getCustomerName()];
        $adminEmail = $this->scopeConfig->getValue(
            self::ASKIT_ADMIN_NOTIFICATION_EMAIL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store->getCode()
        );

        if (!$adminEmail || !$this->emailAddressValidator->isValid($adminEmail)) {
            return $this;
        }

        $to = ['email' => $adminEmail, 'name' => ''];
        $templateId = $this->scopeConfig->getValue(
            self::ASKIT_ADMIN_NOTIFICATION_EMAIL_TEMPLATE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store->getCode()
        );

        $question = $question->load($question->getId());

        $_item = $this->urlHelper->get($question->getItemTypeId(), $question->getItemId(), false);

        if (null == $question->getParentId()) {
            $subject = 'New %ss question was posted : %s';
        } else {
            $subject = '%ss question was updated : %s';
        }
            $prefix = $question->getEntityTypeLabel($question->getItemTypeId());
        if (!empty($prefix)) {
            $prefix = $prefix->getText();
        } else {
            $prefix = '';
        }
        $subject = sprintf($subject, $prefix, $_item['label']);

        $vars = [
            'subject'      => $subject,
            'user_name'    => $question->getCustomerName(),
            'user_email'   => $question->getEmail(),
            'question'     => $question->getText(),
            'answer'       => $message->hasData('answer') ? $message->getData('answer') : false,
            'item_name'    => $_item['label'],
            'item_url'     => $_item['href']
        ];
        $this->appEmulation->stopEnvironmentEmulation();

        $this->_sendEmail($from, $to, $templateId, $vars, $store, $area);

        return $this;
    }
}

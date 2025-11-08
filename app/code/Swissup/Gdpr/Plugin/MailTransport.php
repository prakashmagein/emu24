<?php

namespace Swissup\Gdpr\Plugin;

class MailTransport
{
    /**
     * @var \Swissup\Gdpr\Helper\Data
     */
    private $helper;

    /**
     * @param \Swissup\Gdpr\Helper\Data $helper
     */
    public function __construct(
        \Swissup\Gdpr\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Framework\Mail\TransportInterface $subject
     * @param callable $proceed
     */
    public function aroundSendMessage(
        \Magento\Framework\Mail\TransportInterface $subject,
        callable $proceed
    ) {
        if (!$this->helper->isGdprEnabled()) {
            return $proceed();
        }

        $email = false;
        $message = $subject->getMessage();

        if (method_exists($message, 'getTo')) {
            $address = $message->getTo();

            if (is_array($address)) {
                $address = current($address);
            }

            if (method_exists($address, 'getEmail')) {
                $email = $address->getEmail();
            }
        }

        if (!$email || !is_string($email) || !$this->helper->isEmailAnonymized($email)) {
            return $proceed();
        }
    }
}

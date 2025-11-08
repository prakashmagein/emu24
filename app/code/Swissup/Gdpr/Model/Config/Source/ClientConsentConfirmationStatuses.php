<?php

namespace Swissup\Gdpr\Model\Config\Source;

use Swissup\Gdpr\Model\ClientConsent;

class ClientConsentConfirmationStatuses implements \Magento\Framework\Option\ArrayInterface
{
    const PENDING = 0;
    const CONFIRMED = 1;
    const NOT_APPLICABLE = 2;

    public function toOptionArray()
    {
        return [
            ['value' => self::PENDING, 'label' => 'Awaiting Confirmation'],
            ['value' => self::CONFIRMED, 'label' => 'Confirmed'],
            ['value' => self::NOT_APPLICABLE, 'label' => 'Not Applicable'],
        ];
    }
}

<?php

namespace Swissup\Gdpr\Block;

use Magento\Framework\View\Element\Template;

class GoogleConsent extends Template
{
    protected $_template = 'Swissup_Gdpr::google-consent.phtml';

    private \Swissup\Gdpr\Helper\Data $helper;

    private \Magento\Framework\Serialize\Serializer\Json $json;

    public function __construct(
        Template\Context $context,
        \Swissup\Gdpr\Helper\Data $helper,
        \Magento\Framework\Serialize\Serializer\Json $json,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helper = $helper;
        $this->json = $json;
    }

    public function getTemplate()
    {
        if (!$this->helper->isGoogleConsentEnabled()) {
            return '';
        }
        return $this->_template;
    }

    public function getDefaultValues()
    {
        try {
            $values = $this->json->unserialize($this->helper->getGoogleConsentDefaults());
        } catch (\Exception $e) {
            $values = [[
                'ad_storage' => 'denied',
                'ad_user_data' => 'denied',
                'ad_personalization' => 'denied',
                'analytics_storage' => 'denied',
            ]];
        }

        $result = [];
        foreach ($values as $value) {
            $result[] = $this->json->serialize($value);
        }

        return $result;
    }
}

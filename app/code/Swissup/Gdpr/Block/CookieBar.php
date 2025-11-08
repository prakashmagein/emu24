<?php

namespace Swissup\Gdpr\Block;

use Magento\Framework\View\Element\Template;

class CookieBar extends Template
{
    protected $_template = 'Swissup_Gdpr::cookie-bar.phtml';

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var \Swissup\Gdpr\Helper\Data
     */
    protected $helper;

    /**
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Swissup\Gdpr\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->jsonEncoder = $jsonEncoder;
        $this->helper = $helper;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        if (!$this->helper->isCookieConsentEnabled()) {
            return '';
        }
        return $this->_template;
    }

    public function getCssClass()
    {
        $classes = [
            'cookie-bar',
            'cookie-bar-mode-' . $this->getDisplayMode(),
            'cookie-bar-theme-' . $this->helper->getCookieBarTheme(),
        ];

        return implode(' ', $classes);
    }

    public function isCookieWallEnabled()
    {
        return $this->helper->isCookieWallEnabled();
    }

    public function getDisplayMode()
    {
        return $this->helper->getCookieBarDisplayMode();
    }

    public function getContentHtml()
    {
        return $this->helper->getCookieBarText();
    }

    public function getCookieSettingsPageUrl()
    {
        return $this->getUrl($this->helper->getCookieSettingsUrlPath());
    }

    public function getJsonConfig()
    {
        return $this->jsonEncoder->encode([]);
    }
}

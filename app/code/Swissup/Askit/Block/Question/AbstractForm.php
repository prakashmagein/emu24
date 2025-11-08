<?php

namespace Swissup\Askit\Block\Question;

class AbstractForm extends AbstractBlock
{
    /**
     * @var string
     */
    protected $formId;

    /**
     * @var \Swissup\Askit\Helper\Form
     */
    protected $formHelper;

    /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    protected $urlEncoder;

    /**
     * @var \Swissup\Askit\Model\CaptchaProvider
     */
    protected $captchaProvider;

    public function __construct(
        Context $context,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Swissup\Askit\Model\CaptchaProvider $captchaProvider,
        array $data = []
    ) {
        $this->urlEncoder = $urlEncoder;
        $this->captchaProvider = $captchaProvider;
        parent::__construct($context, $data);
    }

    /**
     * @return mixed|string
     */
    private function getReferParam()
    {
        $request = $this->getRequest();
        $currentUrl = $this->_urlBuilder->getCurrentUrl();
        $referUrlParam = $this->urlEncoder->encode($currentUrl);

        $isAjax = $request->isAjax() || (bool) $request->getParam('ajax', false);
        $previousReferParam = $request->getParam('referer', false);
        if ($isAjax && $previousReferParam) {
            $referUrlParam = $previousReferParam;
        }

        return $referUrlParam;
    }

    /**
     * Return login URL
     *
     * @return string
     */
    public function getLoginLink()
    {
        $referUrlParam = $this->getReferParam();
        return $this->getUrl(
            'customer/account/login/',
            [\Magento\Customer\Model\Url::REFERER_QUERY_PARAM_NAME => $referUrlParam]
        );
    }

    /**
     * Return register URL
     *
     * @return string
     */
    public function getRegisterUrl()
    {
        $referUrlParam = $this->getReferParam();
        return $this->_urlBuilder->getUrl(
            'customer/account/create',
            [\Magento\Customer\Model\Url::REFERER_QUERY_PARAM_NAME => $referUrlParam]
        );
    }

    /**
     * Get config for component 'Swissup_Askit/js/view/captcha'.
     *
     * @return array
     */
    public function getCaptchaConfig()
    {
        return $this->captchaProvider->getConfig($this->formId);
    }
}

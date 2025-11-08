<?php

namespace Swissup\Askit\Model;

use Magento\Captcha\Helper\Data as HelperCaptcha;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;

class CaptchaProvider
{
    private ModuleManager $moduleManager;
    private ObjectManagerInterface $objectManager;
    private StoreManagerInterface $storeManager;

    public function __construct(
        ModuleManager $moduleManager,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager
    ) {
        $this->moduleManager = $moduleManager;
        $this->objectManager = $objectManager;
        $this->storeManager = $storeManager;
    }

    public function getConfig(string $formId): array
    {
        $recaptchaUiConfigResolver = $this->getRecaptchaConfigResolver($formId);
        if ($recaptchaUiConfigResolver
            && $settings = $recaptchaUiConfigResolver->get($formId)
        ) {
            return [
                'component' => 'Magento_ReCaptchaFrontendUi/js/reCaptcha',
                'displayArea' => 'additional-form-fields',
                'reCaptchaId' => uniqid('recaptcha-'),
                'settings' => $settings
            ];
        }

        $captcha = $this->getCaptcha($formId);
        if ($captcha && $captcha->isRequired()) {
            $captcha->generate();
            $store = $this->storeManager->getStore();

            return [
                'component' => 'Swissup_Askit/js/view/captcha',
                'displayArea' => 'additional-form-fields',
                'formId' => $formId,
                'configSource' => [
                    'isCaseSensitive' => (boolean)$captcha->isCaseSensitive(),
                    'imageHeight' => (int)$captcha->getHeight(),
                    'imageSrc' => $captcha->getImgSrc(),
                    'refreshUrl' => $store->getUrl('captcha/refresh', ['_secure' => $store->isCurrentlySecure()]),
                    'isRequired' => (boolean)$captcha->isRequired()
                ]
            ];
        }

        return [];
    }

    public function getCaptcha(string $formId)
    {
        if ($this->moduleManager->isEnabled('Magento_Captcha')) {
            $helperCaptcha = $this->objectManager->get(HelperCaptcha::class);
            $captcha = $helperCaptcha->getCaptcha($formId);

            return $captcha;
        }

        return null;
    }

    public function getRecaptchaConfigResolver(string $formId)
    {
        if ($this->moduleManager->isEnabled('Magento_ReCaptchaUi')) {
            $isCaptchaEnabled = $this->objectManager->get(
                \Magento\ReCaptchaUi\Model\IsCaptchaEnabledInterface::class
            );
            if ($isCaptchaEnabled->isCaptchaEnabledFor($formId)) {
                $recaptchaUiConfigResolver = $this->objectManager->get(
                    \Magento\ReCaptchaUi\Model\UiConfigResolverInterface::class
                );

                return $recaptchaUiConfigResolver;
            }
        }

        return null;
    }
}

<?php

namespace Swissup\Gdpr\Plugin;

use Magento\Framework\App\Area;
use Magento\Framework\Stdlib\Cookie\PublicCookieMetadata;
use Magento\Framework\Stdlib\Cookie\SensitiveCookieMetadata;

class CookieBlocker
{
    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;

    /**
     * @var \Swissup\Gdpr\Helper\Data
     */
    private $helper;

    /**
     * @var \Swissup\Gdpr\Model\CookieManager
     */
    private $cookieManager;

    /**
     * @var \Swissup\Gdpr\Model\BlockedCookieRepository
     */
    private $blockedCookieRepository;

    /**
     * @param \Magento\Framework\App\State $appState
     * @param \Swissup\Gdpr\Helper\Data $helper
     * @param \Swissup\Gdpr\Model\CookieManager $cookieManager
     * @param \Swissup\Gdpr\Model\BlockedCookieRepository $blockedCookieRepository
     */
    public function __construct(
        \Magento\Framework\App\State $appState,
        \Swissup\Gdpr\Helper\Data $helper,
        \Swissup\Gdpr\Model\CookieManager $cookieManager,
        \Swissup\Gdpr\Model\BlockedCookieRepository $blockedCookieRepository
    ) {
        $this->appState = $appState;
        $this->helper = $helper;
        $this->cookieManager = $cookieManager;
        $this->blockedCookieRepository = $blockedCookieRepository;
    }

    /**
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $subject
     * @param callable $proceed
     * @param string $name
     * @param string $value
     * @param SensitiveCookieMetadata|null $metadata
     * @return mixed
     */
    public function aroundSetSensitiveCookie(
        \Magento\Framework\Stdlib\CookieManagerInterface $subject,
        callable $proceed,
        $name,
        $value,
        ?SensitiveCookieMetadata $metadata = null
    ) {
        if ($this->isAllowed($name)) {
            return $proceed($name, $value, $metadata);
        }

        $this->registerCookie($name);
    }

    /**
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $subject
     * @param callable $proceed
     * @param string $name
     * @param string $value
     * @param PublicCookieMetadata|null $metadata
     * @return mixed
     */
    public function aroundSetPublicCookie(
        \Magento\Framework\Stdlib\CookieManagerInterface $subject,
        callable $proceed,
        $name,
        $value,
        ?PublicCookieMetadata $metadata = null
    ) {
        if ($this->isAllowed($name)) {
            return $proceed($name, $value, $metadata);
        }

        $this->registerCookie($name);
    }

    /**
     * @param string $name
     * @return void
     */
    private function registerCookie($name)
    {
        $this->blockedCookieRepository->registerCookie([
            'name' => $name,
            'description' => 'Some PHP script made an attempt to create this cookie'
        ]);
    }

    /**
     * @param string $name
     * @return boolean
     */
    private function isAllowed($name)
    {
        if ($this->appState->getAreaCode() !== Area::AREA_FRONTEND) {
            return true;
        }

        if (!$this->helper->isCookieConsentEnabled()) {
            return true;
        }

        return $this->cookieManager->isAllowed($name);
    }
}

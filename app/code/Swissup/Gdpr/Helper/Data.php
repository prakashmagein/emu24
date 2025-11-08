<?php

namespace Swissup\Gdpr\Helper;

use Magento\Store\Model\ScopeInterface;
use Swissup\Gdpr\Model\ClientRequest;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var string
     */
    const CONFIG_PATH_ENABLED = 'swissup_gdpr/general/enabled';

    /**
     * @var string
     */
    const CONFIG_PATH_CONSENTS = 'swissup_gdpr/consents';

    /**
     * @var string
     */
    const CONFIG_PATH_COOKIE_CONSENT_ENABLED = 'swissup_gdpr/cookie_consents/enabled';

    /**
     * @var string
     */
    const CONFIG_PATH_COOKIE_CONSENT_LIFETIME = 'swissup_gdpr/cookie_consents/lifetime';

    /**
     * @var string
     */
    const CONFIG_PATH_GOOGLE_CONSENT_ENABLED = 'swissup_gdpr/cookie_consents/google_consent';

    /**
     * @var string
     */
    const CONFIG_PATH_COOKIE_CONSENT_TITLE = 'swissup_gdpr/consents/cookie/title';

    /**
     * @var string
     */
    const CONFIG_PATH_COOKIE_BAR_DISPLAY_MODE = 'swissup_gdpr/cookie_consents/cookie_bar_display_mode';

    /**
     * @var string
     */
    const CONFIG_PATH_COOKIE_BAR_TEXT_FULL = 'swissup_gdpr/cookie_consents/cookie_bar_text_full';

    /**
     * @var string
     */
    const CONFIG_PATH_COOKIE_BAR_TEXT_MINIMALISTIC = 'swissup_gdpr/cookie_consents/cookie_bar_text_minimalistic';

    /**
     * @var string
     */
    const CONFIG_PATH_COOKIE_SETTINGS_URL_PATH = 'swissup_gdpr/cookie_consents/cookie_page_url';

    /**
     * @var string
     */
    const CONFIG_PATH_COOKIE_SETTINGS_TEXT = 'swissup_gdpr/cookie_consents/cookie_page_text';

    /**
     * @var string
     */
    const CONFIG_PATH_COOKIE_SETTINGS_COLUMNS_COUNT = 'swissup_gdpr/cookie_consents/cookie_page_columns_count';

    /**
     * @var string
     */
    const CONFIG_PATH_ANONYMIZATION_PLACEHOLDER = 'swissup_gdpr/request/delete_data/placeholder';

    /**
     * @var \Swissup\Gdpr\Model\CookieGroupRepository
     */
    private $cookieGroupRepository;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Swissup\Gdpr\Model\CookieGroupRepository $cookieGroupRepository
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Swissup\Gdpr\Model\CookieGroupRepository $cookieGroupRepository
    ) {
        parent::__construct($context);
        $this->cookieGroupRepository = $cookieGroupRepository;
    }

    /**
     * Check if module is enabled
     *
     * @return boolean
     */
    public function isGdprEnabled()
    {
        return $this->getConfigValue(self::CONFIG_PATH_ENABLED);
    }

    /**
     * Get all consents
     *
     * @return array
     */
    public function getConsents()
    {
        return $this->getConfigValue(self::CONFIG_PATH_CONSENTS);
    }

    /**
     * Check if module is enabled
     *
     * @return boolean
     */
    public function isCookieConsentEnabled()
    {
        return $this->isGdprEnabled()
            && $this->getConfigValue(self::CONFIG_PATH_COOKIE_CONSENT_ENABLED);
    }

    /**
     * @return boolean
     */
    public function isGoogleConsentEnabled()
    {
        return $this->isCookieConsentEnabled()
            && $this->getConfigValue(self::CONFIG_PATH_GOOGLE_CONSENT_ENABLED);
    }

    public function getGoogleConsentDefaults()
    {
        return trim($this->getConfigValue('swissup_gdpr/cookie_consents/google_consent_defaults'));
    }

    /**
     * @return int Days
     */
    public function getCookieConsentLifetime()
    {
        return $this->getConfigValue(self::CONFIG_PATH_COOKIE_CONSENT_LIFETIME);
    }

    /**
     * @return string
     */
    public function getCookieConsentTitle(array $groups)
    {
        $title = $this->getConfigValue(self::CONFIG_PATH_COOKIE_CONSENT_TITLE);

        if (strpos($title, '{{cookie_groups}}') !== false) {
            $renderedGroups = [];
            $collection = $this->cookieGroupRepository->getList();

            foreach ($groups as $group) {
                $item = $collection->getItemByColumnValue('code', $group);
                if ($item) {
                    $renderedGroups[] = $item->getTitle();
                }
            }

            $title = str_replace('{{cookie_groups}}', implode(', ', $renderedGroups), $title);
        }

        return $title;
    }

    /**
     * @return string
     */
    public function getCookieBarDisplayMode()
    {
        return $this->getConfigValue(self::CONFIG_PATH_COOKIE_BAR_DISPLAY_MODE);
    }

    /**
     * @return boolean
     */
    public function isCookieWallEnabled()
    {
        return (bool) $this->getConfigValue('swissup_gdpr/cookie_consents/cookie_wall_enabled');
    }

    /**
     * @return string
     */
    public function getCookieBarText()
    {
        $mode = $this->getCookieBarDisplayMode();
        $mapping = [
            'full' => self::CONFIG_PATH_COOKIE_BAR_TEXT_FULL,
            'minimalistic' => self::CONFIG_PATH_COOKIE_BAR_TEXT_MINIMALISTIC,
        ];
        return $this->getConfigValue($mapping[$mode] ?? $mapping['minimalistic']);
    }

    /**
     * @return string
     */
    public function getCookieBarTheme()
    {
        return 'light';
    }

    /**
     * @return string
     */
    public function getCookieSettingsUrlPath()
    {
        return $this->getConfigValue(self::CONFIG_PATH_COOKIE_SETTINGS_URL_PATH);
    }

    /**
     * @return string
     */
    public function getCookieSettingsText()
    {
        return $this->getConfigValue(self::CONFIG_PATH_COOKIE_SETTINGS_TEXT);
    }

    /**
     * @return string
     */
    public function getCookieSettingsColumnsCount()
    {
        return $this->getConfigValue(self::CONFIG_PATH_COOKIE_SETTINGS_COLUMNS_COUNT);
    }

    /**
     * Get placeholoder for anonymized data
     *
     * @return string
     */
    public function getAnonymizationPlaceholder()
    {
        return $this->getConfigValue(self::CONFIG_PATH_ANONYMIZATION_PLACEHOLDER);
    }

    /**
     * Get specific config value
     *
     * @param  string $path
     * @param  string $scope
     * @return string
     */
    public function getConfigValue($path, $scope = ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->getValue($path, $scope);
    }

    /**
     * @param string $email
     * @return boolean
     */
    public function isEmailAnonymized($email)
    {
        return strpos($email, ClientRequest::ANONYMIZED_IDENTITY_PREFIX) === 0 &&
            strpos($email, ClientRequest::ANONYMIZED_IDENTITY_SUFFIX) !== false;
    }
}

<?php
namespace Swissup\Pagespeed\Model\View\Asset;

use Magento\Framework\View\Asset\Minification as DefaultMinification;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Swissup\Pagespeed\Helper\Config as ConfigHelper;

/**
 * Helper class for static files minification related processes.
 * @api
 */
class Minification extends DefaultMinification
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var State
     */
    private $appState;

    /**
     * @var ConfigHelper
     */
    private $configHelper;

    /**
     * @var string
     */
    private $scope;

    /**
     * @var array
     */
    private $configCache = [];

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param State $appState
     * @param ConfigHelper $configHelper
     * @param string $scope
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        State $appState,
        ConfigHelper $configHelper,
        $scope = 'store'
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->appState = $appState;
        $this->configHelper = $configHelper;
        $this->scope = $scope;

        parent::__construct($scopeConfig, $appState, $scope);
    }

    /**
     * Check whether asset minification is on for specified content type
     *
     * @param string $contentType
     * @return bool
     */
    public function isEnabled($contentType)
    {
        if (!isset($this->configCache[self::XML_PATH_MINIFICATION_ENABLED][$contentType])) {
            $this->configCache[self::XML_PATH_MINIFICATION_ENABLED][$contentType] =
                ($this->appState->getMode() != State::MODE_DEVELOPER || $this->configHelper->isDeveloperModeIgnored()) &&
                (bool)$this->scopeConfig->isSetFlag(
                    sprintf(self::XML_PATH_MINIFICATION_ENABLED, $contentType),
                    $this->scope
                );
        }

        return $this->configCache[self::XML_PATH_MINIFICATION_ENABLED][$contentType];
    }
}

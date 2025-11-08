<?php
namespace Swissup\Pagespeed\Plugin\Framework\App;

class ConfigPlugin
{
    private const XML_PATH_USE_CSS_CRITICAL_PATH = 'dev/css/use_css_critical_path';

    /**
     * @var \Swissup\Pagespeed\Helper\Config
     */
    private $configHelper;

    /**
     * @param \Swissup\Pagespeed\Helper\Config $configHelper
     */
    public function __construct(
        \Swissup\Pagespeed\Helper\Config $configHelper
    ) {
        $this->configHelper = $configHelper;
    }

    /**
     * Retrieve config flag in \Magento\Theme\Controller\Result\AsyncCssPlugin
     *
     * @param \Magento\Framework\App\Config $subject
     * @param bool $result
     * @param string $path
     * @return bool
     */
    public function afterIsSetFlag(
        \Magento\Framework\App\Config $subject, $result, $path
    ) {
        if ($path === self::XML_PATH_USE_CSS_CRITICAL_PATH &&
            !$this->configHelper->isAllowedCriticalCssOnCurrentPage()
        ) {
            return false;
        }

        return $result;
    }
}

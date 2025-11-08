<?php
namespace Swissup\Amp\Plugin\Framework;

class Url
{
    /**
     * @var \Swissup\Amp\Helper\Data
     */
    protected $helper;

    /**
     * @param \Swissup\Amp\Helper\Data $helper
     */
    public function __construct(
        \Swissup\Amp\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Build and cache url by requested path and parameters
     *
     * @param   \Magento\Framework\UrlInterface $subject
     * @param   string|null $routePath
     * @param   array|null $routeParams
     * @return  string
     */
    public function beforeGetUrl(
        \Magento\Framework\UrlInterface $subject,
        $routePath = null,
        $routeParams = null
    ) {
        if ($this->canAddAmpParameter($routePath)) {
            if (null === $routeParams) {
                $routeParams = [];
            }
            if (!isset($routeParams['_query']) || !$routeParams['_query']) {
                $routeParams['_query'] = [];
            }

            // do not use isset here, to allow to set the NULL
            if (!array_key_exists('amp', $routeParams['_query'])) {
                $routeParams['_query']['amp'] = 1;
            }
        }

        return [$routePath, $routeParams];
    }

    /**
     * Check if `amp=1` parameter should be added
     *
     * @param string|null $routePath
     * @return boolean
     */
    protected function canAddAmpParameter($routePath = null)
    {
        if ($this->helper->isVarnishRequest() && $this->helper->isPersistentBrowsingEnabled()) {
            return true;
        }

        if (!$this->helper->canUseAmp() || !$this->helper->isPersistentBrowsingEnabled()) {
            return false;
        }

        return true;
    }
}

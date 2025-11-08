<?php

namespace Swissup\Gdpr\Controller;

class Router implements \Magento\Framework\App\RouterInterface
{
    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    protected $actionFactory;

    /**
     * Page view helper
     *
     * @var \Swissup\Gdpr\Helper\Data
     */
    protected $helper;

    /**
     * @param \Magento\Framework\App\ActionFactory $actionFactory
     * @param \Swissup\Gdpr\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Swissup\Gdpr\Helper\Data $helper
    ) {
        $this->actionFactory = $actionFactory;
        $this->helper = $helper;
    }

    /**
     * Match firecheckout page
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return bool
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        if (!$this->helper->isCookieConsentEnabled()) {
            return null;
        }

        $currentPath = trim($request->getPathInfo(), '/');
        $cookiePath = $this->helper->getCookieSettingsUrlPath();
        if ($currentPath !== $cookiePath) {
            return null;
        }

        $request->setAlias(
            \Magento\Framework\UrlInterface::REWRITE_REQUEST_PATH_ALIAS,
            $currentPath
        );
        $request->setPathInfo('/privacy-tools/cookie/index');

        return $this->actionFactory->create(
            \Magento\Framework\App\Action\Forward::class
        );
    }
}

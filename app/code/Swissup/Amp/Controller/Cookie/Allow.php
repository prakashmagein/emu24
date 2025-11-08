<?php
namespace Swissup\Amp\Controller\Cookie;

class Allow extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Cookie\Helper\Cookie
     */
    protected $cookieHelper;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $cookieManager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    protected $cookieMetadataFactory;

    /**
     * @var \Magento\Framework\Session\Config\ConfigInterface
     */
    protected $sessionConfig;

    /**
     * @var \Swissup\Amp\Helper\Data
     */
    protected $ampHelper;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Cookie\Helper\Cookie $cookieHelper
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Framework\Session\Config\ConfigInterface $sessionConfig
     * @param \Swissup\Amp\Helper\Data $ampHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Cookie\Helper\Cookie $cookieHelper,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\Session\Config\ConfigInterface $sessionConfig,
        \Swissup\Amp\Helper\Data $ampHelper
    ) {
        $this->cookieHelper = $cookieHelper;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->sessionConfig = $sessionConfig;
        $this->ampHelper = $ampHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        $cookieMetadata = $this->cookieMetadataFactory
            ->createPublicCookieMetadata()
            ->setDuration($this->cookieHelper->getCookieRestrictionLifetime())
            ->setPath($this->sessionConfig->getCookiePath())
            ->setDomain($this->sessionConfig->getCookieDomain());

        $this->cookieManager->setPublicCookie(
            \Magento\Cookie\Helper\Cookie::IS_USER_ALLOWED_SAVE_COOKIE,
            $this->cookieHelper->getAcceptedSaveCookiesWebsiteIds(),
            $cookieMetadata
        );

        return $this->getResponse()
            ->setHttpResponseCode(200)
            ->setHeader('Access-Control-Allow-Origin', $this->ampHelper->getAmpCacheDomainName())
            ->setHeader('Access-Control-Allow-Credentials', 'true')
            ->setHeader('Access-Control-Expose-Headers', 'AMP-Access-Control-Allow-Source-Origin')
            ->setHeader(
                'Amp-Access-Control-Allow-Source-Origin',
                $this->getRequest()->getScheme() . '://' . $this->getRequest()->getHttpHost()
            );
    }
}

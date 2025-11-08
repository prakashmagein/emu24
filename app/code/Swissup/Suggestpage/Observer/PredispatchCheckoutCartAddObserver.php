<?php
namespace Swissup\Suggestpage\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

use Magento\Checkout\Model\Session as checkoutSession;
use Magento\Framework\UrlInterface;
use Swissup\Suggestpage\Helper\Config as ConfigHelper;

class PredispatchCheckoutCartAddObserver implements ObserverInterface
{
    /**
     * @var checkoutSession
     */
    protected $checkoutSession;

    /**
     *
     * @var UrlInterface
     */
    protected $urlManager;

    /**
     *
     * @var UrlInterface
     */
    protected $configHelper;

    /**
     * Constructor
     *
     * @param checkoutSession $checkoutSession
     * @param UrlInterface $urlManager
     * @param ConfigHelper $configHelper
     */
    public function __construct(
        checkoutSession $checkoutSession,
        UrlInterface $urlManager,
        ConfigHelper $configHelper
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->urlManager = $urlManager;
        $this->configHelper = $configHelper;
    }

    /**
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        if (!$this->configHelper->isShowAfterAddToCart()) {
            return $this;
        }
        /** @var $request \Magento\Framework\App\RequestInterface */
        $request = $observer->getEvent()->getRequest();
        if ($request->isAjax()) {
            return $this;
        }
        /** @var \Magento\Framework\App\Action\Action $controller */
        $controller = $observer->getControllerAction();

        /** @var $response \Magento\Framework\App\ResponseInterface */
        $response = $controller->getResponse();

        $url = $this->urlManager->getUrl('suggest/index/index');

        $response->setRedirect($url);
        $this->checkoutSession->setNoCartRedirect(true);

        return $this;
    }
}

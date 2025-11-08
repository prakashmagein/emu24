<?php
namespace Swissup\Suggestpage\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

use Magento\Checkout\Model\Session as checkoutSession;
use Magento\Framework\UrlInterface;
use Magento\Framework\Registry;
use Swissup\Suggestpage\Helper\Config as ConfigHelper;

class CheckoutCartSaveAfterObserver implements ObserverInterface
{
    /**
     * @var checkoutSession
     */
    protected $checkoutSession;

    /**
     *
     * @var UrlInterface
     */
    protected $configHelper;

    /**
     *
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * Constructor
     *
     * @param checkoutSession $checkoutSession
     * @param ConfigHelper $configHelper
     * @param Registry $registry
     */
    public function __construct(
        checkoutSession $checkoutSession,
        ConfigHelper $configHelper,
        Registry $registry
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->configHelper = $configHelper;
        $this->registry = $registry;
    }

    /**
     *
     * @param Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Observer $observer)
    {
        if (!$this->configHelper->isShowAfterAddToCart()) {
            return $this;
        }
        $items = $this->registry->registry('suggestpage_last_quote_items');
        if (!is_array($items) || 0 == count($items)) {
            return $this;
        }
        $ids = [];
        foreach ($items as $item) {
            if ($item->getParentItemId()) {
                continue;
            }
            $ids[] = $item->getId();
        }
        $this->checkoutSession->setSuggestpageQuoteItemIds($ids);
        return $this;
    }
}

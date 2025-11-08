<?php
namespace Swissup\Suggestpage\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

use Magento\Framework\UrlInterface;
use Magento\Framework\Registry;
use Swissup\Suggestpage\Helper\Config as ConfigHelper;

class SalesQuoteProductAddAfterObserver implements ObserverInterface
{
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
     * @param ConfigHelper $configHelper
     * @param Registry $registry
     */
    public function __construct(
        ConfigHelper $configHelper,
        Registry $registry
    ) {
        $this->configHelper = $configHelper;
        $this->registry = $registry;
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
        $items = $observer->getItems();
        if (0 == count($items)) {
            return $this;
        }

        $this->registry->unregister('suggestpage_last_quote_items');
        $this->registry->register('suggestpage_last_quote_items', $items);

        return $this;
    }
}

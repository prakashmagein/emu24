<?php
namespace Swissup\Askit\Observer\Adminhtml\Catalog\Category\Tab;

use Magento\Framework\Event\ObserverInterface;

use Swissup\Askit\Api\Data\MessageInterface;

class Questions implements ObserverInterface
{

    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\View\Element\Context $context
     */
    public function __construct(\Magento\Framework\View\Element\Context $context)
    {
        $this->urlBuilder = $context->getUrlBuilder();
    }

    /**
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $tabs = $observer->getEvent()->getTabs();
        $url = $this->urlBuilder->getUrl('askit/question/grid', [
            '_current' => true,
            'item_type_id' => MessageInterface::TYPE_CATALOG_CATEGORY
        ]);
        $tabs->addTab('questions', [
            'label' => __('Questions'),
            'url' => $url,
            'class' => 'ajax'
        ]);

        return $this;
    }
}

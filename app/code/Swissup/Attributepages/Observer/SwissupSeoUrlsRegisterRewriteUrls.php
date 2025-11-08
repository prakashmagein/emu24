<?php

namespace Swissup\Attributepages\Observer;

class SwissupSeoUrlsRegisterRewriteUrls implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\UrlRewrite\Model\UrlRewriteFactory
     */
    private $rewriteFactory;

    /**
     * @var \Swissup\Attributepages\Model\PageParamsExtractor
     */
    private $pageParamsExtractor;

    /**
     * @var \Swissup\Attributepages\Helper\Page\View
     */
    private $pageViewHelper;

    /**
     * @param \Magento\UrlRewrite\Model\UrlRewriteFactory $rewriteFactory
     * @param \Swissup\Attributepages\Model\PageParamsExtractor $pageParamsExtractor
     * @param \Swissup\Attributepages\Helper\Page\View $pageViewHelper
     */
    public function __construct(
        \Magento\UrlRewrite\Model\UrlRewriteFactory $rewriteFactory,
        \Swissup\Attributepages\Model\PageParamsExtractor $pageParamsExtractor,
        \Swissup\Attributepages\Helper\Page\View $pageViewHelper
    ) {
        $this->rewriteFactory = $rewriteFactory;
        $this->pageParamsExtractor = $pageParamsExtractor;
        $this->pageViewHelper = $pageViewHelper;
    }

    /**
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $params = $this->pageParamsExtractor->extract();
        $page = $this->pageViewHelper->initPagesInRegistry(
            isset($params[1]) ? $params[1] : $params[0], // current_page
            isset($params[1]) ? $params[0] : false,      // parent_page
            'identifier'
        );

        if (!$page) {
            return;
        }

        $data = [
            'entity_type' => 'swissup_attributepage',
            'entity_id' => $page->getId(),
            'request_path' => $params[0],
            'target_path' => 'attributepages/page/view/page_id/' . $page->getId(),
            'redirect_type' => 0,
        ];

        if ($page->getParentPage()) {
            $data['entity_type'] = 'swissup_attributepage_option';
            $data['request_path'] .= '/' . $params[1];
            $data['target_path'] .= '/parent_id/' . $page->getParentPage()->getId();
        }

        $observer->getCollection()->addItem(
            $this->rewriteFactory->create(['data' => $data])
        );
    }
}

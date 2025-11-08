<?php

namespace Swissup\Attributepages\Plugin;

class ProductUrl
{
    /**
     * @var \Swissup\Attributepages\Helper\Page\View $pageViewHelper
     */
    private $pageViewHelper;

    /**
     * @param \Swissup\Attributepages\Helper\Page\View $pageViewHelper
     */
    public function __construct(
        \Swissup\Attributepages\Helper\Page\View $pageViewHelper
    ) {
        $this->pageViewHelper = $pageViewHelper;
    }

    /**
     * @param \Magento\Catalog\Model\Product\Url $subject
     * @param \Magento\Catalog\Model\Product $product
     * @param array $params
     * @return mixed
     */
    public function beforeGetUrl(
        \Magento\Catalog\Model\Product\Url $subject,
        \Magento\Catalog\Model\Product $product,
        $params = []
    ) {
        $currentPage = $this->pageViewHelper->getRegistryObject('attributepages_current_page');
        if (!$currentPage) {
            return;
        }

        if (!is_array($params)) {
            $params = [];
        }

        // fix non seo links, when "Use Categories Path for Product URLs" is enabled,
        // since there are no links to the products inside root category.
        $params['_ignore_category'] = true;
        if ($requestPath = $product->getRequestPath()) {
            $product->setRequestPath(ltrim($requestPath, '/'));
        }

        return [$product, $params];
    }
}

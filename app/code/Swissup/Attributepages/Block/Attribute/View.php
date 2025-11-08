<?php

namespace Swissup\Attributepages\Block\Attribute;

use Magento\Store\Model\ScopeInterface;
use Swissup\Attributepages\Model\Entity as AttributepagesModel;

class View extends \Swissup\Attributepages\Block\AbstractBlock
{
    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $filterProvider;

    /**
     * @var \Magento\Catalog\Model\Layer
     */
    protected $catalogLayer;

    /**
     * @var \Magento\Catalog\Helper\Category
     */
    protected $categoryHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Swissup\Attributepages\Model\ResourceModel\Entity\CollectionFactory $attrpagesCollectionFactory
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param \Magento\Catalog\Helper\Category $categoryHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Swissup\Attributepages\Model\ResourceModel\Entity\CollectionFactory $attrpagesCollectionFactory,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Catalog\Helper\Category $categoryHelper,
        array $data = []
    ) {
        parent::__construct($context, $coreRegistry, $attrpagesCollectionFactory, $data);
        $this->filterProvider = $filterProvider;
        $this->catalogLayer = $layerResolver->get();
        $this->categoryHelper = $categoryHelper;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        if ($this->categoryHelper->canUseCanonicalTag()
            && $page = $this->getCurrentPage()
        ) {
            $this->pageConfig->addRemotePageAsset(
                $page->getUrl(),
                'canonical',
                [
                    'attributes' => [
                        'rel' => 'canonical'
                    ]
                ]
            );
        }

        return parent::_prepareLayout();
    }

    protected function _beforeToHtml()
    {
        $list = $this->getChild('children_list');

        if ($list) {
            $list->setCurrentPage($this->getCurrentPage());
        }

        return parent::_beforeToHtml();
    }

    public function getPageDescription()
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $html = $this->filterProvider->getBlockFilter()
            ->setStoreId($storeId)
            ->filter($this->getCurrentPage()->getContent());

        return $html;
    }

    public function getHideDescriptionWhenFilterIsUsed()
    {
        $section = $this->getCurrentPage()->isAttributeBasedPage() ? 'option_list' : 'product_list';
        $key = "attributepages/{$section}/hide_description_when_filter_is_used";
        return $this->_scopeConfig->getValue($key, ScopeInterface::SCOPE_STORE);
    }

    public function canShowDescription()
    {
        $page = $this->getCurrentPage();
        if ($page->isChildrenMode()) {
            return false;
        }

        $hasContent = (bool)$page->getContent();
        if (!$hasContent) {
            return false;
        }

        /**
         * don't show the block:
         *  if pagination is used
         *  if filter is applied
         */
        $page = (int)$this->getRequest()->getParam('p', 1);
        if ($this->getHideDescriptionWhenFilterIsUsed()
            && ($page > 1
                || count($this->catalogLayer->getState()->getFilters()))
        ) {
            return false;
        }

        return $hasContent;
    }

    public function canShowChildren()
    {
        return !(bool)$this->getCurrentPage()->isDescriptionMode();
    }
}

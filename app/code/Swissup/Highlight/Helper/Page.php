<?php

namespace Swissup\Highlight\Helper;

use Magento\Framework\App\Action\Action;
use Magento\Catalog\Helper\Category as CategoryHelper;

class Page extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var array
     */
    protected $configValues;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Swissup\Highlight\Model\Page\Collection
     */
    protected $pageCollection;

    /**
     * @var CategoryHelper
     */
    protected $categoryHelper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context      $context
     * @param \Magento\Framework\Escaper                 $escaper
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Swissup\Highlight\Model\Page\Collection   $pageCollection
     * @param CategoryHelper                             $categoryHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Swissup\Highlight\Model\Page\Collection $pageCollection,
        CategoryHelper $categoryHelper
    ) {
        $this->escaper = $escaper;
        $this->resultPageFactory = $resultPageFactory;
        $this->pageCollection = $pageCollection;
        $this->categoryHelper = $categoryHelper;
        parent::__construct($context);
    }

    /**
     * @param Action $action
     * @param string $pageType
     * @return \Magento\Framework\View\Result\Page|bool
     */
    public function preparePage(Action $action, $pageType)
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $pageConfig = $resultPage->getConfig();

        $config = $this->getPageByType($pageType);
        $pageConfig->getTitle()->set($config['meta_title'] ?? $config['title']);
        $pageConfig->setKeywords($config['meta_keywords'] ?? '');
        $pageConfig->setDescription($config['meta_description'] ?? '');
        if ($this->categoryHelper->canUseCanonicalTag()) {
            $pageConfig->addRemotePageAsset(
                $this->getPageUrl($pageType),
                'canonical',
                ['attributes' => ['rel' => 'canonical']]
            );
        }

        $pageMainTitle = $resultPage->getLayout()->getBlock('page.main.title');
        if ($pageMainTitle) {
            $pageMainTitle->setPageTitle($this->escaper->escapeHtml($config['title']));
        }

        $pageDescription = $resultPage->getLayout()->getBlock('page.description');
        if ($pageDescription) {
            $pageDescription->setText($config['description'] ?? '');
        }

        $products = $resultPage->getLayout()->getBlock('category.products.list');
        if ($products) {
            $products
                ->setCacheLifetime(null)
                ->setIsWidget(false);

            if (isset($config['min_popularity'])) {
                $products->setMinPopularity((int)$config['min_popularity']);
            }
            if (isset($config['period'])) {
                $products->setPeriod($config['period']);
            }
        }

        return $resultPage;
    }

    /**
     * Retrieve action name for specific page type
     *
     * @param  string $pageType
     * @return string
     */
    public function getActionName($pageType)
    {
        $page = $this->pageCollection->getItemByColumnValue('type', $pageType);
        return $page ? $page->getActionName() : '';
    }

    /**
     * Retrieve page url for specific page type
     *
     * @param  string $pageType
     * @return string
     */
    public function getPageUrl($pageType)
    {
        $urlKey = $this->getUrlKey($pageType);

        return $urlKey ? $this->getDirectUrl($urlKey) : false;
    }

    /**
     * Retrieve direct url for for custom url
     *
     * @param  string $url
     * @return string
     */
    public function getDirectUrl($url)
    {
        return $this->_urlBuilder->getUrl(null, ['_direct' => $url]);
    }

    /**
     * Retrieve page url key for specific page type
     *
     * @param  string $pageType
     * @return string|null
     */
    public function getUrlKey($pageType)
    {
        $page = $this->getPageByType($pageType);

        return $page ? $page->getUrl() : null;
    }

    public function getPageTypeByUrlKey($urlKey)
    {
        $page = $this->pageCollection->getItemByColumnValue('url', $urlKey);

        return $page ? $page->getType() : false;
    }

    private function getPageByType($pageType)
    {
        return $this->pageCollection->getItemByColumnValue('type', $pageType);
    }
}

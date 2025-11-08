<?php
namespace Swissup\Attributepages\Controller\Page;

class View extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Swissup\Attributepages\Helper\Page\View
     */
    protected $pageViewHelper;

    /**
     * @var \Magento\Framework\Controller\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Swissup\Attributepages\Helper\Page\View $pageViewHelper
     * @param \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Swissup\Attributepages\Helper\Page\View $pageViewHelper,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->pageViewHelper = $pageViewHelper;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->storeManager = $storeManager;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    protected function _initPage()
    {
        if (!$page = $this->pageViewHelper->getRegistryObject('attributepages_current_page')) {
            // links with rewrite disabled: attributepages/page/view/page_id/10/parent_id/1/
            $page = $this->pageViewHelper->initPagesInRegistry(
                (int) $this->getRequest()->getParam('page_id', false),
                (int) $this->getRequest()->getParam('parent_id', false),
                'entity_id'
            );
        }
        return $page;
    }

    public function execute()
    {
        if (!$page = $this->_initPage()) {
            return $this->resultForwardFactory->create()->forward('noroute');
        }

        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $layout = $resultPage->getLayout();
        $update = $layout->getUpdate();
        $pageConfig = $resultPage->getConfig();
        $parentPage = $page->getParentPage();

        $resultPage->addHandle('default')
            ->addHandle('ATTRIBUTEPAGES_PAGE_' . $page->getId());
        $resultPage->addPageLayoutHandles(['id' => $page->getId()]);
        if ($page->isAttributeBasedPage()) {
            $resultPage->addHandle('attributepages_attribute_page');
        } else {
            $resultPage->addHandle('attributepages_option_page');

            if ($page->canUseLayeredNavigation()) {
                $update->addHandle('attributepages_option_page_layered');
            } else {
                $update->addHandle('attributepages_option_page_default');
            }
        }

        if ($handle = $page->getRootTemplate()) {
            $pageConfig->setPageLayout($handle);
        }

        $layoutUpdate = (string) $page->getLayoutUpdateXml();
        if (!empty($layoutUpdate)) {
            $update->addUpdate($layoutUpdate);
        }

        if ($page->isAttributeBasedPage()) {
            $suffix = '-attribute-page';
        } else {
            $suffix = '-option-page';
        }
        $pageConfig->addBodyClass('attributepages-' . $suffix);
        $pageConfig->addBodyClass('attributepages-' . $page->getIdentifier());

        if ($breadcrumbs = $layout->getBlock('breadcrumbs')) {
            $breadcrumbs->addCrumb('home', [
                'label' => __('Home'),
                'title' => __('Go to Home Page'),
                'link'  => $this->storeManager->getStore()->getBaseUrl()
            ]);
            if ($parentPage) {
                $breadcrumbs->addCrumb('parent_page', [
                    'label' => $parentPage->getTitle(),
                    'title' => $parentPage->getTitle(),
                    'link'  => $parentPage->getUrl()
                ]);
            }
            $breadcrumbs->addCrumb('current_page', [
                'label' => $page->getTitle(),
                'title' => $page->getTitle()
            ]);
        }

        $pageConfig->getTitle()->set($page->getMetaTitle() ?: $page->getTitle());
        $pageConfig->setKeywords($page->getMetaKeywords());
        $pageConfig->setDescription($page->getMetaDescription());

        $pageMainTitle = $layout->getBlock('page.main.title');
        if ($pageMainTitle &&
            (strpos($layoutUpdate, '"setPageTitle"') === false ||
            strpos($layoutUpdate, '"page.main.title"') === false)
        ) {
            $pageMainTitle->setPageTitle($page->getPageTitle());
        }

        return $resultPage;
    }
}

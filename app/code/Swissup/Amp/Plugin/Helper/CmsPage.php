<?php
namespace Swissup\Amp\Plugin\Helper;

use Magento\Cms\Helper\Page as CmsHelper;
use Magento\Framework\App\Action\Action as AppAction;

class CmsPage
{
    /**
     * @var \Swissup\Amp\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Cms\Model\Page
     */
    protected $page;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Swissup\Amp\Helper\Data $helper
     * @param \Magento\Cms\Model\Page $page
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Swissup\Amp\Helper\Data $helper,
        \Magento\Cms\Model\Page $page,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->helper = $helper;
        $this->page = $page;
        $this->storeManager = $storeManager;
    }

    /**
     * Return result CMS page
     *
     * CmsHelper $subject
     * @param AppAction $action
     * @param null $pageId
     * @return array
     */
    public function beforePrepareResultPage(
        CmsHelper $subject,
        AppAction $action,
        $pageId = null
    ) {
        if (!$this->helper->canUseAmp()) {
            return [$action, $pageId];
        }

        $fullActionName = $action->getRequest()->getFullActionName();
        if ($fullActionName == 'cms_index_index') {
            $pageId = $this->helper->getHomepageId();
        }

        if ($fullActionName == 'cms_page_view') {
            $homepage = $this->page
                ->setStoreId($this->storeManager->getStore()->getId())
                ->load($this->helper->getConfig(CmsHelper::XML_PATH_HOME_PAGE));

            if ($homepage->getId() == $pageId) {
                $pageId = $this->helper->getHomepageId();
            }
        }

        return [$action, $pageId];
    }
}

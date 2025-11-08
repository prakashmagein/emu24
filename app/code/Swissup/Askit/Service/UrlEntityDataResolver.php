<?php
namespace Swissup\Askit\Service;

use Swissup\Askit\Api\Data\MessageInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

class UrlEntityDataResolver
{
    /**
     * Page factory
     *
     * @var \Magento\Cms\Model\PageFactory
     */
    private $pageFactory;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\UrlRewrite\Model\UrlFinderInterface
     */
    private $urlFinder;

    /**
     * @param \Magento\Cms\Model\PageFactory $pageFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\UrlRewrite\Model\UrlFinderInterface $urlFinder
     */
    public function __construct(
        \Magento\Cms\Model\PageFactory $pageFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\UrlRewrite\Model\UrlFinderInterface $urlFinder
    ) {

        $this->pageFactory = $pageFactory;
        $this->storeManager = $storeManager;
        $this->urlFinder = $urlFinder;
    }


    /**
     * @param $identifier
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function resolve($identifier)
    {
        $storeId = $this->storeManager->getStore()->getId();
        /** @var \Magento\Cms\Model\Page $page */
        $page = $this->pageFactory->create();
        $pageId = $page->checkIdentifier($identifier, $storeId);
        if ($pageId) {
            return [
                'item_type_id' => MessageInterface::TYPE_CMS_PAGE,
                'page_id' => $pageId
            ];
        }
        $urlRewriteEntity = $this->urlFinder->findOneByData([
            UrlRewrite::REQUEST_PATH => trim($identifier, '/'),
            UrlRewrite::STORE_ID => $storeId,
        ]);
        if (!$urlRewriteEntity) {
            return [];
        }
        $type = $urlRewriteEntity->getEntityType();
        $id = $urlRewriteEntity->getEntityId();
        if ('product' === $type) {
            return [
                'item_type_id' => MessageInterface::TYPE_CATALOG_PRODUCT,
                'id' => $id
            ];
        }

        if ('category' === $type) {
            return [
                'item_type_id' => MessageInterface::TYPE_CATALOG_CATEGORY,
                'id' => $id
            ];
        }
        return [];
    }
}

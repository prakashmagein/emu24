<?php
namespace Swissup\Askit\Controller\Adminhtml\Message;

use Magento\Framework\Controller\ResultFactory;
use Swissup\Askit\Api\Data\MessageInterface;

abstract class AbstractGrid extends \Magento\Backend\App\Action
{
    /**
     * @var string
     */
    protected $gridBlockName = '';

    /**
     *
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        /** @var \Magento\Framework\App\Request\Http $request */
        $request = $this->getRequest();
        $type = $request->getParam('item_type_id', false);

        /** @var \Magento\Framework\View\Result\Layout $resultLayout */
        $resultLayout = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
        /** @var \Magento\Framework\View\Element\AbstractBlock $grid */
        $grid = $resultLayout->getLayout()->getBlock($this->gridBlockName);
        $grid->setUseAjax(true);
        switch ($type) {
            case 'customer':
                $customerId = $request->getParam('customer_id', false);
                $grid->setCustomerId($customerId);
                break;
            case MessageInterface::TYPE_CMS_PAGE:
                $pageId = $request->getParam('page_id', false);
                $grid->setPageId($pageId);
                break;
            case MessageInterface::TYPE_CATALOG_CATEGORY:
                $categoryId = $request->getParam('id', false);
                $grid->setCategoryId($categoryId);
                break;
            case MessageInterface::TYPE_CATALOG_PRODUCT:
            default:
                $productId = $request->getParam('id', false);
                $grid->setProductId($productId);
                break;
        }
        return $resultLayout;
    }
}

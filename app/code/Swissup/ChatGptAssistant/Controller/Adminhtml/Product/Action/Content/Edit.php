<?php
namespace Swissup\ChatGptAssistant\Controller\Adminhtml\Product\Action\Content;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Swissup\ChatGptAssistant\Controller\Adminhtml\Product\Action\Attribute as AttributeAction;

class Edit extends AttributeAction implements HttpGetActionInterface, HttpPostActionInterface
{
    protected \Magento\Framework\View\Result\PageFactory $resultPageFactory;

    protected Filter $filter;

    protected CollectionFactory $collectionFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Catalog\Helper\Product\Edit\Action\Attribute $attributeHelper,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Filter $filter,
        CollectionFactory $collectionFactory
    ) {
        parent::__construct($context, $attributeHelper);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if ($this->getRequest()->getParam('filters')) {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $this->attributeHelper->setProductIds($collection->getAllIds());
        }

        if (!$this->_validateProducts()) {
            return $this->resultRedirectFactory->create()->setPath('catalog/product/', ['_current' => true]);
        }
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Select Fields to Generate Content'));

        return $resultPage;
    }
}

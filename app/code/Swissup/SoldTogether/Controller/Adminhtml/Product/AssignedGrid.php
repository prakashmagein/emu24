<?php

namespace Swissup\SoldTogether\Controller\Adminhtml\Product;

use Magento\Framework\Controller\ResultFactory;
use Swissup\SoldTogether\Block\Adminhtml\AssignedProducts\Grid;
use Swissup\SoldTogether\Model\Resolver\ResourceModels as ResourceResolver;

class AssignedGrid extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var ResourceResolver
     */
    protected $resourceResolver;

    /**
     * @param \Magento\Backend\App\Action\Context             $context
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Framework\View\LayoutFactory           $layoutFactory
     * @param ResourceResolver                                $resourceResolver
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        ResourceResolver $resourceResolver
    ) {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
        $this->layoutFactory = $layoutFactory;
        $this->resourceResolver = $resourceResolver;
    }

    /**
     * Grid Action
     * Display list of products related to current category
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $productId = $this->getRequest()->getParam('product');
        $linkType = $this->getRequest()->getParam('link_type');
        $resource = $this->resourceResolver->get($linkType);
        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();
        if (!$resource) {
            return $resultRaw->setContents('false');
        }

        $linkedData = $resource->readLinkedData(
            $productId,
            ['related_id']
        );

        return $resultRaw->setContents(
            $this->layoutFactory->create()->createBlock(
                Grid::class,
                "soldtogether.{$linkType}.grid",
                [
                    'data' => [
                        'id' => "soldtogether_{$linkType}_grid",
                        'link_type' => $linkType,
                        'selected_products' => array_keys($linkedData),
                        'current_product_id' => $productId
                    ]
                ]
            )->toHtml()
        );
    }
}
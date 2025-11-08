<?php

namespace Swissup\Ajaxpro\CustomerData;

use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Swissup\Ajaxpro\CustomerData\AbstractSectionData;

class Product extends AbstractSectionData
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * Manager messages
     * @var MessageManager
     */
    protected $messageManager;

    /**
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Magento\Framework\View\Layout\BuilderFactory $layoutBuilderFactory
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\View\Page\Layout\Reader $pageLayoutReader
     * @param \Swissup\Ajaxpro\Helper\Config $configHelper
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Registry $coreRegistry
     * @param MessageManager $messageManager
     * @param array $data
     * @codeCoverageIgnore
     */
    public function __construct(
        \Swissup\Ajaxpro\Model\View\LayoutFactory $layoutFactory,
        \Magento\Framework\View\Layout\BuilderFactory $layoutBuilderFactory,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\View\Page\Layout\Reader $pageLayoutReader,
        \Swissup\Ajaxpro\Helper\Config $configHelper,
        ProductRepositoryInterface $productRepository,
        \Magento\Framework\Registry $coreRegistry,
        MessageManager $messageManager,
        array $data = []
    ) {
        parent::__construct(
            $layoutFactory,
            $layoutBuilderFactory,
            $context,
            $pageLayoutReader,
            $configHelper,
            $data
        );
        $this->productRepository = $productRepository;
        $this->storeManager = $context->getStoreManager();
        $this->coreRegistry = $coreRegistry;
        $this->messageManager = $messageManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {
        if ((!$this->configHelper->isProductViewEnabled()
                && !$this->configHelper->isQuickViewEnabled()
            ) || !in_array('ajaxpro-product', $this->getSectionNames())
        ) {
            return [];
        }

        $ajaxpro = $this->getRequest()->getParam('ajaxpro');

        $return  = [];
        if (isset($ajaxpro['product_id'])) {
            try {
                /** @var \Magento\Framework\App\Request\Http $request */
                $request = $this->getRequest();
                $request->setParam('id', $ajaxpro['product_id']);
                $product = $this->productRepository->getById(
                    $ajaxpro['product_id'],
                    false,
                    $this->storeManager->getStore()->getId()
                );

                $this->coreRegistry->register('current_product', $product);
                $this->coreRegistry->register('product', $product);
            } catch (NoSuchEntityException $e) {
                return [];
            }
            $productHandles = [];

            $urlSafeSku = rawurlencode($product->getSku());
            $productHandles = array_merge(
                ['default'],
                $this->generatePageLayoutHandles(
                    ['id' => $product->getId(), 'sku' => $urlSafeSku, 'type' => $product->getTypeId()],
                    'catalog_product_view'
                ),
                ['ajaxpro_catalog_product_view']
            );

            /** @var \Magento\Theme\Block\Html\Title $pageMainTitle */
            $pageMainTitle = $this->setHandles($productHandles)->getBlockInstance('page.main.title');
            if ($pageMainTitle) {
                $pageMainTitle->setPageTitle($product->getName());
            }

            $return  = [
                // 'params' => $ajaxpro,
                // 'test' => md5(time()),
                'catalog.product.view' => $this->setHandles($productHandles)->getBlockHtml('content'),
                //'catalog.product.view.handles' => $productHandles,
                'reinit' => $this->setHandles(['default'])->getBlockHtml('ajaxpro.init')
            ];
            $this->flushLayouts();
        }
        return $return;
    }
}

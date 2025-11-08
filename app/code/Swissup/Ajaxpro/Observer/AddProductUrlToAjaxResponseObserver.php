<?php
namespace Swissup\Ajaxpro\Observer;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Zend\Http\Header\HeaderInterface as HttpHeaderInterface;

class AddProductUrlToAjaxResponseObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json $serializer
     */
    private $serializer;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Swissup\Ajaxpro\Helper\Config
     */
    protected $configHelper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
     * @param ProductRepositoryInterface $productRepository
     * @param StoreManagerInterface $storeManager
     * @param \Swissup\Ajaxpro\Helper\Config $configHelper
     */
    public function __construct(
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager,
        \Swissup\Ajaxpro\Helper\Config $configHelper
    ) {
        $this->serializer = $serializer;
        $this->productRepository = $productRepository;
        $this->storeManager =  $storeManager;
        $this->configHelper = $configHelper;
    }

    /**
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        if (!$this->configHelper->isProductViewEnabled()) {
            return $this;
        }

        $event = $observer->getEvent();
        /** @var \Magento\Framework\App\Request\Http $request */
        $request = $event->getRequest();
        if (!$request->isAjax() || $request->getParam('return_url')) {
            return $this;
        }

        /** @var \Magento\Framework\App\Action\Action $controller */
        $controller = $event->getControllerAction();
        /** @var \Magento\Framework\App\Response\Http $response */
        $response = $controller->getResponse();

        $resultJsonData = $response->getContent();
        if (empty($resultJsonData)) {
            return $this;
        }
        json_decode($resultJsonData, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this;
        }
        $resultJsonData = $this->serializer->unserialize($resultJsonData);

        if (!isset($resultJsonData['backUrl'])) {
            return $this;
        }

        $actionName = $request->getFullActionName();
        if ($actionName === 'checkout_cart_add' &&
            $this->configHelper->isRedirectToCartEnabled()
        ) {
            return $this;
        }
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->initProduct($request);
        if (!$product || !$this->isProductHasOptions($product)) {
            return $this;
        }
        $resultJsonData['action'] = $actionName;

        $resultJsonData['ajaxpro']['product'] = [
            'id' => $product->getId(),
            'product_url' => $this->getProductUrl($product),
            'has_options' => true
        ];

        // $cacheControlHeader = $response->getHeader('Cache-Control');

        $resultJsonData = $this->serializer->serialize($resultJsonData);
        $response
            // ->clearHeaders()
            ->clearHeader('Location')
            ->setHttpResponseCode(200);
        $response
            ->representJson($resultJsonData)
            // ->send()
            ;
        // if ($cacheControlHeader instanceof HttpHeaderInterface) {
        //     $response->setHeader('Cache-Control', $cacheControlHeader->getFieldValue());
        // }
        // exit;
        return $this;
    }

    /**
     * @param $product
     * @return mixed
     */
    private function getProductUrl($product)
    {
        $additional = [];
        $additional['_escape'] = true;
        $additional['_query'] = [];
        $additional['_query']['options'] = 'cart';

        return $product->getUrlModel()->getUrl($product, $additional);
    }

    /**
     * Initialize product instance from request data
     *
     * @return \Magento\Catalog\Api\Data\ProductInterface|false
     */
    protected function initProduct(\Magento\Framework\App\RequestInterface $request)
    {
        $productId = (int) $request->getParam('product');
        if ($productId) {
            $storeId = $this->storeManager->getStore()->getId();
            try {
                return $this->productRepository->getById($productId, false, $storeId);
            } catch (NoSuchEntityException $e) {
                return false;
            }
        }
        return false;
    }

    /**
     *
     * @param  \Magento\Catalog\Model\Product  $product
     * @return boolean
     */
    protected function isProductHasOptions(\Magento\Catalog\Model\Product $product)
    {
        if ($product->getTypeID() === 'grouped') {
            return true;
        }
        $typeInstance = $product->getTypeInstance();
        return $typeInstance && ($typeInstance->hasRequiredOptions($product) || $typeInstance->hasOptions($product));
    }
}

<?php

namespace Swissup\SoldTogetherEmail\Block;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Swissup\SoldTogether\Model\Config\Provider as ConfigProvider;
use Swissup\SoldTogether\Block\Context;

class Order extends \Swissup\SoldTogether\Block\Related
{
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param ConfigProvider           $configProvider
     * @param Context                  $context
     * @param array                    $data
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        ConfigProvider $configProvider,
        Context $context,
        array $data = []
    ) {
        $this->orderRepository = $orderRepository;
        parent::__construct($configProvider, $context, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getProduct()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        $this->dataProvider->setOrderId($this->getOrderId());
        if ($this->getProductsCount() > 0) {
            return parent::getItems();
        }

        return [];
    }

    /**
     * Get image HTML
     *
     * @param  ProductInterface $product
     * @return string
     */
    public function prepareImageHtml(ProductInterface $product): string
    {
        $imageId = $this->getData('image_id') ?: 'mini_cart_product_thumbnail';
        $imageHtml = $this->imageBuilder
            ->setProduct($product) // compatibility with Magento 2.2.6
            ->setImageId($imageId) // compatibility with Magento 2.2.6
            ->create()             // compatibility with Magento 2.2.6
            ->setIsRenderingEmail(true)
            ->setArea('frontend')
            ->toHtml();

        // strip tag <script>
        $html = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $imageHtml);

        return $html;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductsCount()
    {
        return (int)$this->getData('products_count');
    }

    /**
     * Block title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getData('title') ?:
            __('Customers who bought this product also commonly purchased the following combination of items');
    }
}


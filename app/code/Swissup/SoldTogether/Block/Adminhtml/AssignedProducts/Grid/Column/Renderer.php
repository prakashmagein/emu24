<?php

namespace Swissup\SoldTogether\Block\Adminhtml\AssignedProducts\Grid\Column;

use Magento\Backend\Block\Context;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Locale\CurrencyInterface;

class Renderer extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Price
{
    private $productRepository;
    private $searchCriteriaBuilder;
    /**
     * @var int
     */
    protected $_defaultWidth = 100;

    /**
     * @var ImageHelper
     */
    protected $imageHelper;

    /**
     * @var array
     */
    private $products = [];

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param SearchCriteriaBuilder      $searchCriteriaBuilder
     * @param ImageHelper                $imageHelper
     * @param Context                    $context
     * @param array                      $data
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ImageHelper $imageHelper,
        Context $context,
        CurrencyInterface $localeCurrency,
        array $data = []
    ) {
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->imageHelper = $imageHelper;
        parent::__construct($context, $localeCurrency, $data);
    }

    /**
     * Renders grid column
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $product = $this->getProduct($row->getId());
        $thumbnailHtml = "<img src=\"{$this->getThumbnailUrl($row)}\" />";
        $priceHtml = '<span class="price">' . $this->getFormattedPrice($row) . '</span>';
        $productNameHtml = '<span class="product-name">' . ($product ? $product->getName() : '[Not Found]') . '</span>';
        $skuHtml = '<span class="sku">' . ($product ? $product->getSku() : '[not-found]') . '</span>';

        return "<div class=\"handle\"></div>" .
            "<div class=\"thumbnail-wrapper\">{$thumbnailHtml}</div>" .
            "<div class=\"sku-wrapper\">{$skuHtml}</div>" .
            "<div class=\"product-name-wrapper\">{$productNameHtml}</div>" .
            "<div class=\"price-wrapper\">{$priceHtml}</div>";
    }

    private function getProduct($productId)
    {
        if (empty($this->products)) {
            $grid = $this->getColumn()->getGrid();
            $collection = $grid->getCollection();
            $ids = $collection->getColumnValues('entity_id');
            $criteria = $this->searchCriteriaBuilder
                ->addFilter('entity_id', implode(',', $ids), 'in')
                ->create();
            $this->products = $this->productRepository->getList($criteria)->getItems();
        }

        return $this->products[$productId] ?? null;
    }

    private function getThumbnailUrl(\Magento\Framework\DataObject $row)
    {
        $product = $this->getProduct($row->getId());

        return $product ?
            $this->imageHelper->init(
                $product,
                'product_listing_thumbnail'
            )->getUrl() :
            $this->imageHelper->getDefaultPlaceholderUrl();
    }

    private function getFormattedPrice(\Magento\Framework\DataObject $row)
    {
        $product = $this->getProduct($row->getId());
        $currencyCode = $this->_getCurrencyCode($row);
        if (!$currencyCode || !$product) {
            return '';
        }

        $price = (float)$product->getPrice() * $this->_getRate($row);
        $price = sprintf("%f", $price);

        return $this->_localeCurrency
            ->getCurrency($currencyCode)
            ->toCurrency($price);
    }

    /**
     * Renders CSS
     *
     * @return string
     */
    public function renderCss()
    {
        $css = parent::renderCss();

        return str_replace(' col-price', '', $css);
    }
}


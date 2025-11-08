<?php

namespace Swissup\SoldTogether\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\DataObject;
use Swissup\SoldTogether\Model\ResourceModel\Order\Collection as OrderCollection;

class PromotedPrice
{
    /**
     * @var array
     */
    private $price = [];

    /**
     * @var array
     */
    private $dbItem = [];

    /**
     * @var OrderCollection
     */
    private $orderCollection;

    /**
     * @param OrderCollection $orderCollection
     */
    public function __construct(
        OrderCollection $orderCollection
    ) {
        $this->orderCollection = $orderCollection;
    }

    /**
     * @param  ProductInterface        $product
     * @param  string|ProductInterface $promoter
     * @param  string                  $relationType
     * @return float
     */
    public function get(
        ProductInterface $product,
        $promoter,
        string $relationType
    ) {
        $key = $this->_buildKey([
            $relationType,
            is_object($promoter) ? $promoter->getId() : $promoter,
            $product->getId()
        ]);
        if (!isset($this->price[$key])) {
            $this->price[$key] = $this->_get($product, $promoter, $relationType);
        }

        return $this->price[$key];
    }

    /**
     * @param  ProductInterface        $product
     * @param  string|ProductInterface $promoter
     * @param  string                  $relationType
     * @return float
     */
    private function _get(
        ProductInterface $product,
        $promoter,
        string $relationType
    ) {
        $config = new DataObject();
        if ($product->hasData('soldtogether_data')) {
            $config->setData($product->getSoldtogetherData());
        } else {
            $productId = is_object($promoter) ? $promoter->getId() : $promoter;
            /* When child of configurable then get patent_id else entity_id. */
            $relatedId = $product->getParentId() ?: $product->getId();
            $item = $this->_readItemFromDb($productId, $relatedId, $relationType);
            $config->setData($item->getDataSerialized());
        }

        return $this->_calculatePrice($product, $config);
    }

    /**
     * @param  int|string                                           $productId
     * @param  int|string                                           $relatedId
     * @param  string                                               $relationType
     * @return \Swissup\SoldTogether\Model\AbstractModel|DataObject
     */
    private function _readItemFromDb(
        $productId,
        $relatedId,
        string $relationType = 'order'
    ) {
        $key = $this->_buildKey([$relationType, $productId, $relatedId]);
        if (!isset($this->dbItem[$key])) {
            $item = new DataObject();
            switch ($relationType) {
                case 'order':
                    $item = $this->orderCollection->getNewEmptyItem();
                    $item->loadRelation(
                            $productId,
                            $relatedId,
                            \Magento\Store\Model\Store::DEFAULT_STORE_ID
                        );
                    break;
            }

            $this->dbItem[$key] = $item;
        }

        return $this->dbItem[$key];
    }

    private function _calculatePrice(
        ProductInterface $product,
        DataObject $config
    ): ?float {
        $promoValue = (float)$config->getPromoValue();
        switch ($config->getPromoRule()) {
            case 'by_percent':
                $price = $product->getData('price') * (100 - $promoValue) / 100;
                break;

            case 'by_fixed':
                $price = $product->getData('price') - $promoValue;
                $price = ($price <= 0) ? 0.01 : $price;
                break;

            case 'to_percent':
                $price = $product->getData('price') * $promoValue / 100;
                break;

            case 'to_fixed':
                $price = $promoValue;
                break;

            default:
                $price = null;
                break;
        }

        return $price;
    }

    private function _buildKey(array $parts): string
    {
        $parts = array_filter($parts);

        return implode(':', $parts);
    }
}

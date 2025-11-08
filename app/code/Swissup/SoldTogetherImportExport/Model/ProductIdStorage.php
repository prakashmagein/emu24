<?php

namespace Swissup\SoldTogetherImportExport\Model;

use Magento\Catalog\Model\ProductIdLocatorInterface;

class ProductIdStorage
{
    /**
     * @var ProductIdLocatorInterface
     */
    private $productIdLocator;

    /**
     * @var array
     */
    private $ids = [];

    /**
     * @param ProductIdLocatorInterface $productIdLocator
     */
    public function __construct(
        ProductIdLocatorInterface $productIdLocator
    ) {
        $this->productIdLocator = $productIdLocator;
    }

    /**
     * Load data to storage
     *
     * @param  array $bunch
     * @return void
     */
    public function load(array $bunch)
    {
        $sku = [];
        foreach ($bunch as $row) {
            $sku[$row['product_sku']] = true;
            $sku[$row['related_sku']] = true;
        }

        $this->ids = $this->productIdLocator
            ->retrieveProductIdsBySkus(
                array_keys($sku)
            );
    }

    /**
     * Get product id by its SKU
     *
     * @param  string $sku
     * @return string|null
     */
    public function getId($sku)
    {
        return array_key_first($this->ids[$sku] ?? []);
    }
}

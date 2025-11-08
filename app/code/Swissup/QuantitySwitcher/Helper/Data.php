<?php
namespace Swissup\QuantitySwitcher\Helper;

use Magento\CatalogInventory\Api\StockRegistryInterface as StockRegistry;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Registry;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Data extends AbstractHelper
{
    /**
     * Global minimum allowed qty in shopping cart xpath config
     */
    const GLOBAL_MIN_QTY = 'cataloginventory/item_options/min_sale_qty';

    /**
     * Global maximum allowed qty in shopping cart xpath config
     */
    const GLOBAL_MAX_QTY = 'cataloginventory/item_options/max_sale_qty';

    /**
     * Global qty increments xpath config
     */
    const GLOBAL_QTY_INC = 'cataloginventory/item_options/qty_increments';

    /**
     * Quantity switcher type xpath config
     */
    const XML_PATH_QTY_TYPE = 'quantityswitcher/general/switcher_type';

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param Context               $context
     * @param StockRegistry         $stockRegistry
     * @param Registry              $registry
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        StockRegistry $stockRegistry,
        Registry $registry,
        StoreManagerInterface $storeManager
    ) {
        $this->registry = $registry;
        $this->stockRegistry = $stockRegistry;
        $this->storeManager = $storeManager;

        parent::__construct($context);
    }

    protected function getConfig($key)
    {
        return $this->scopeConfig->getValue(
            $key,ScopeInterface::SCOPE_STORE
        );
    }

    public function isEnabled(): bool
    {
        return (bool) $this->getConfig('quantityswitcher/general/enabled');
    }

    public function isEnabledOnCart(): bool
    {
        return $this->isEnabled() && $this->getConfig('quantityswitcher/general/enabled_cart');
    }

    public function getSwitcherType(): string
    {
        return (string) $this->getConfig(self::XML_PATH_QTY_TYPE);
    }

    /**
     * Get global minimum allowed sale qty config
     *
     * @return int
     */
    public function getGlobalMinQty()
    {
        return abs((int)$this->getConfig(self::GLOBAL_MIN_QTY));
    }

    /**
     * Get global maximum allowed sale qty config
     *
     * @return int
     */
    public function getGlobalMaxQty()
    {
        return abs((int)$this->getConfig(self::GLOBAL_MAX_QTY));
    }

    /**
     * Get global qty increments config
     *
     * @return int
     */
    public function getGlobalQtyInc()
    {
        return abs((int)$this->getConfig(self::GLOBAL_QTY_INC));
    }

    /**
     * Get minimum allowed sale qty config for specific product
     *
     * @param Magento\CatalogInventory\Model\Stock\Item $stockItem
     * @return float
     */
    public function getMinQty($stockItem)
    {
        return (float)$stockItem->getMinSaleQty();
    }

    /**
     * Get maximum allowed sale qty config for specific product
     *
     * @param Magento\Catalog\Model\Product $product
     * @return float
     */
    public function getMaxQty($product)
    {
        $stockItem = $this->stockRegistry->getStockItem($product->getId());

        $manageStock = $stockItem->getManageStock();
        $maxSaleQty = $stockItem->getMaxSaleQty();
        $backOrders = $stockItem->getBackorders();
        $qty = $stockItem->getQty();

        if ($this->isModuleOutputEnabled('Magento_InventorySales')) {
            $objectManager = ObjectManager::getInstance();
            $stockResolver = $objectManager->get(StockResolverInterface::class);
            $productSalableQty = $objectManager->get(GetProductSalableQtyInterface::class);

            $websiteCode = $this->storeManager->getWebsite()->getCode();
            $stock = $stockResolver->execute(SalesChannelInterface::TYPE_WEBSITE, $websiteCode);
            $qty = $productSalableQty->execute($product->getSku(), $stock->getStockId());
        }

        $maxQty = $maxSaleQty;

        if ($manageStock) {
            $maxQty = $maxSaleQty > $qty && !$backOrders ? $qty : $maxSaleQty;
        }

        return $maxQty;
    }

    /**
     * Get qty increments config for specific product
     *
     * @param Magento\CatalogInventory\Model\Stock\Item $stockItem
     * @return int
     */
    public function getQtyIncrements($stockItem)
    {
        $qtyIncEnabled = $stockItem->getEnableQtyIncrements();
        $qtyInc = $stockItem->getQtyIncrements();

        return $qtyIncEnabled ? $qtyInc : 1;
    }

    /**
     * Get stock config array for specific product
     *
     * @return array
     */
    public function getStockConfig()
    {
        $product = $this->registry->registry('product');
        $productId = $product->getId();
        $productType = $product->getTypeId();
        $config[0] = [
            'type' => $productType,
            'switcher' => $this->getSwitcherType()
        ];

        if ($productType == 'configurable' || $productType == 'grouped') {
            $items = ($productType == 'configurable') ?
                $product->getTypeInstance()->getUsedProducts($product) :
                $product->getTypeInstance()->getAssociatedProducts($product);

            foreach($items as $item) {
                $id = $item->getId();
                $stockItem = $this->stockRegistry->getStockItem($id);

                $config[] = array (
                    "id" => $id,
                    "minQty" => $this->getMinQty($stockItem),
                    "maxQty" => $this->getMaxQty($item),
                    "qtyInc" => $this->getQtyIncrements($stockItem)
                );
            }
        } elseif ($productType == 'bundle') {
            $config[] = array (
                "minQty" => $this->getGlobalMinQty(),
                "maxQty" => $this->getGlobalMaxQty(),
                "qtyInc" => $this->getGlobalQtyInc()
            );
        } else {
            $stockItem = $this->stockRegistry->getStockItem($productId);

            $config[] = array (
                "minQty" => $this->getMinQty($stockItem),
                "maxQty" => $this->getMaxQty($product),
                "qtyInc" => $this->getQtyIncrements($stockItem)
            );
        }

        return $config;
    }
}

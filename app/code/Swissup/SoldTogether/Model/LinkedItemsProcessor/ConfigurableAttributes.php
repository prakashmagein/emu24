<?php

namespace Swissup\SoldTogether\Model\LinkedItemsProcessor;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\ObjectManagerInterface;

class ConfigurableAttributes
{
    private JoinProcessorInterface $joinProcessor;
    private ObjectManagerInterface $objectManager;

    /**
     * @param JoinProcessorInterface $joinProcessor
     */
    public function __construct(
        JoinProcessorInterface $joinProcessor,
        ObjectManagerInterface $objectManager
    ) {
        $this->joinProcessor = $joinProcessor;
        $this->objectManager = $objectManager;
    }

    /**
     * Preload configurable attributes for configurable products.
     *
     * @param  array  $items
     * @return void
     */
    public function process(array $items): void
    {
        $configurables = $this->filterConfigurable($items);
        if (empty($configurables)) {
            return;
        }

        /**
         * Inspired by Configurable::getConfigurableAttributes
         */
        $collection = null;
        foreach ($configurables as $configurable) {
            if ($collection) {
                $collection->setProductFilter($configurable);
            } else {
                $collection = $configurable->getTypeInstance()
                    ->getConfigurableAttributeCollection($configurable);
            }
        }

        $this->joinProcessor->process($collection);
        $collection->orderByPosition();

        $keyConfigurableAttributes = $this->getKeyConfigurableAttributes($configurable);
        foreach ($configurables as $configurable) {
            $configurableAttributes = array_reduce(
                $collection->getItemsByColumnValue(
                    'product_id', $configurable->getId()
                ),
                function ($confAttributes, $item) {
                    $confAttributes->addItem($item);
                    return $confAttributes;
                },
                $this->objectManager->create(\Magento\Framework\Data\Collection::class)
            );
            $configurable->setData(
                $keyConfigurableAttributes,
                $configurableAttributes
            );
        }
    }

    private function filterConfigurable(array $items): array
    {
        $products = array_map(function ($item) {
            $product = $item['model'];
            return ($product->getTypeId() == Configurable::TYPE_CODE) ? $product : null;
        }, $items);

        return array_filter($products);
    }

    public function getKeyConfigurableAttributes(ProductInterface $configurable): string
    {
        $typeInstance = $configurable->getTypeInstance();
        $refProperty = new \ReflectionProperty($typeInstance, '_configurableAttributes');
        $refProperty->setAccessible(true);

        return $refProperty->getValue($typeInstance);
    }
}

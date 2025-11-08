<?php declare(strict_types=1);

namespace Swissup\ProLabelsConfigurableProduct\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Swissup\ProLabels\Block\Product\Labels as LabelsBlock;

class Data extends AbstractHelper
{
    public function prepareLabels(
        LabelsBlock $block
    ) : array {
        $superProduct = $block->getCurrentProduct();
        $initialConfig = $this->prepareInitialConfig($block);

        $childProducts = $this->getChildProducts($block);
        // Preload manual labels to reduce number of DB queries
        $block->preloadLabels(array_merge([$superProduct], $childProducts));

        $superlabels = $block->getProductLabels($superProduct);
        $superOptions = [];
        if ($superlabels->getLabelsData()) {
            $superOptions = [
                'labelsData' => $superlabels->getLabelsData(),
                'predefinedVars' => $superlabels->getPredefinedVariables()
            ] + $initialConfig;
        }

        $labels = [];
        if ($superOptions) {
            $labels = [$superProduct->getId() => $superOptions];
        }

        foreach ($childProducts as $product) {
            $childLabels = $block->getProductLabels($product);
            $data = $childLabels->getLabelsData();
            if ($data) {
                $labels[$product->getId()] = [
                    'labelsData' => $data,
                    'predefinedVars' => $childLabels->getPredefinedVariables()
                ] + $initialConfig;
            }
        }

        return $labels;
    }

    private function prepareInitialConfig(
        LabelsBlock $block
    ) : array {
        return [
            'imageLabelsTarget' => $block->getBaseImageWrapConfig(),
            'imageLabelsWrap' => true,
            'imageLabelsRenderAsync' => true,
            'contentLabelsTarget' => $block->getContentWrapConfig()
        ];
    }

    private function getChildProducts(
        LabelsBlock $block
    ) : array {
        $optionsBlock = $block->getLayout()->getBlock('product.info.options.swatches') ?:
            $block->getLayout()->getBlock('product.info.options.configurable');

        return $optionsBlock ?
            $optionsBlock->getAllowProducts() :
            $block->getCurrentProduct()->getTypeInstance()->getUsedProducts($block->getCurrentProduct());
    }
}
<?php

namespace Swissup\SoldTogether\Plugin\Model\Product\Type;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable as Subject;
use Magento\Catalog\Api\Data\ProductInterface;
use Swissup\SoldTogether\Helper\Request;

class Configurable
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @param Request $request
     */
    public function __construct(
        Request $request
    ) {
        $this->request = $request;
    }

    /**
     * Fill buyRequest with attribute values for related products
     *
     * @param  Subject                       $subject
     * @param  \Magento\Framework\DataObject $buyRequest
     * @param  ProductInterface              $product
     * @return null
     */
    public function beforePrepareForCartAdvanced(
        Subject $subject,
        \Magento\Framework\DataObject $buyRequest,
        $product
    ) {
        $relatedIds = $this->request->getRelatedProducts();
        if (!$relatedIds || !in_array($product->getId(), $relatedIds)) {
            return null;
        }

        $superAttributes = $this->request->getRelatedSuperAttributes($product->getId());
        if (!$buyRequest->hasData('super_attribute') && $superAttributes) {
            $buyRequest->setData('super_attribute', $superAttributes);
        }

        return null;
    }

    /**
     * Add soldtogether data from parebt product to sub products.
     *
     * @param  Subject            $subject
     * @param  ProductInterface[] $result
     * @param  ProductInterface   $product
     * @return ProductInterface[]
     */
    public function afterGetUsedProducts(
        Subject $subject,
        $result,
        $product
    ) {
        if (is_array($result) && $product->hasData('soldtogether_data')) {
            $soldtogetherData = $product->getData('soldtogether_data');
            array_map(function ($usedProduct) use ($soldtogetherData) {
                $usedProduct->setData('soldtogether_data', $soldtogetherData);
            }, $result);
        }

        return $result;
    }

    /**
     * Make sure child product has parent id.
     * This is required for adding to cart product with promotion.
     *
     * @param  Subject          $subject
     * @param  ProductInterface $result
     * @param  array            $attributesInfo
     * @param  ProductInterface $product
     * @return ProductInterface
     */
    public function afterGetProductByAttributes(
        Subject $subject,
        $result,
        $attributesInfo,
        $product
    ) {
        if ($result instanceof ProductInterface
            && !$result->hasParentId()
        ) {
            $result->setParentId($product->getId());
        }

        return $result;
    }
}

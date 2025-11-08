<?php

namespace Swissup\SoldTogether\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Request extends AbstractHelper
{
    public function getRelatedProducts(): array
    {
        $related = $this->_request->getParam('related_product');

        return $related ? explode(',', $related) : [];
    }

    public function getProduct(): string
    {
        return $this->_request->getParam('product', '');
    }

    /**
     * Get soldtogether relation type from request param 'soldtogether_promoted'
     *
     * @return string
     */
    public function getPromotedRelation(): string
    {
        return $this->_request->getParam('soldtogether_promoted', '');
    }

    public function getRelatedSuperAttributes($relatedId = null): array
    {
        $superAttributes = json_decode(
            $this->_request->getParam('related_product_super_attribute', '{}'),
            true
        );

        if (!$superAttributes) {
            return [];
        }

        return is_null($relatedId) ?
            $superAttributes :
            ($superAttributes[$relatedId] ?? []);
    }
}

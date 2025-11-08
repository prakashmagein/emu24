<?php
namespace Swissup\Pagespeed\Model\Config\Frontend;

class Pages implements \Magento\Framework\Data\OptionSourceInterface
{
    const ALL_ANOTHER_PAGES = '*_*_*';

    /**
     * Get pages
     *
     * @return array
     */
    public function toOptionArray()
    {
        $result = [];
        foreach ($this->toArray() as $value => $label) {
            $result[] = [
                'value' => $value,
                'label' => $label
            ];
        }

        return $result;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'cms_index_index'            => __('Homepage'),
            'cms_page_view'              => __('Cms Pages'),
            'cms_noroute_index'          => __('Not Found Page'),
            'catalog_category_view'      => __('Category Page'),
            'catalog_product_view'       => __('Product Page'),
            'catalogsearch_result_index' => __('Catalog Search'),
            'checkout_cart_index'        => __('Checkout Cart Page'),
            'checkout_index_index'       => __('Checkout Page'),
            'firecheckout_index_index'   => __('Firecheckout Page'),
            self::ALL_ANOTHER_PAGES      => __('Another Pages'),
        ];
    }
}

<?php
namespace Swissup\Ajaxpro\Model\Item\Source;

class Handle implements \Magento\Framework\Data\OptionSourceInterface
{

    protected function getHandles()
    {
        return [
            'ajaxpro_popup_simple' => 'Simple',
            'ajaxpro_popup_suggestpage_view' => 'SuggestPage Content',
            'ajaxpro_popup_checkout_cart_index_fixes' => 'Shopping Cart',
            'ajaxpro_popup_minicart' => 'Mini Cart',
        ];
    }
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        // $options[] = ['label' => '', 'value' => ''];
        $availableOptions = $this->getHandles();
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return $this->getHandles();
    }
}

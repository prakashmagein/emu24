<?php
namespace Emu24\CreditLimit\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Mode implements ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'sandbox', 'label' => __('Sandbox')],
            ['value' => 'production', 'label' => __('Production')],
        ];
    }
}

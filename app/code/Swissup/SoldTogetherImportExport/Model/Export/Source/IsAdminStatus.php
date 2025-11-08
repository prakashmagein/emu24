<?php

namespace Swissup\SoldTogetherImportExport\Model\Export\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class IsAdminStatus extends AbstractSource
{
    /**
     * {@inheritdoc}
     */
    public function getAllOptions()
    {
        return [
            [
                'value' => 1,
                'label' => __('Yes'),
            ],
            [
                'value' => 0,
                'label' => __('No'),
            ],
        ];
    }
}

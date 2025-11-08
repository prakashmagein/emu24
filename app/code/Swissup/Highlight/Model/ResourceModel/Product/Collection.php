<?php

namespace Swissup\Highlight\Model\ResourceModel\Product;

use Magento\Framework\Data\Collection\AbstractDb;

class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{
    /**
     * @param $fromAttribute
     * @param $toAttribute
     * @param $startTime
     * @param $endTime
     */
    public function addDateRangeFilter($fromAttribute, $toAttribute, $startTime, $endTime)
    {
        if ($fromAttribute && $toAttribute) {
            $this->addAttributeToFilter(
                $fromAttribute,
                [
                    'or' => [
                        0 => ['date' => true, 'to' => $endTime],
                        1 => ['null' => true],
                    ]
                ],
                'left'
            )->addAttributeToFilter(
                $toAttribute,
                [
                    'or' => [
                        0 => ['date' => true, 'from' => $startTime],
                        1 => ['null' => true],
                    ]
                ],
                'left'
            )
            ->addAttributeToFilter(
                [
                    ['attribute' => $fromAttribute, 'notnull' => true],
                    ['attribute' => $toAttribute, 'notnull' => true],
                ]
            );
        } elseif ($fromAttribute) {
            $this->addAttributeToFilter(
                $fromAttribute,
                [
                    'date' => true,
                    'to' => $endTime
                ]
            );
        } elseif ($toAttribute) {
            $this->addAttributeToFilter(
                $toAttribute,
                [
                    'date' => true,
                    'from' => $startTime
                ]
            );
        }

        return $this;
    }
}

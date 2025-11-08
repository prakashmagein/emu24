<?php
namespace Swissup\Highlight\Model\ResourceModel\Product;

/**
 * AddPopularityFilterToCollectionInterface interface
 */
interface AddPopularityFilterToCollectionInterface
{
    /**
     * @param $min
     * @param false $max
     * @return $this
     */
    public function addPopularityFilter($min, $max = false);

    /**
     * @param $period
     * @return $this
     */
    public function addPeriodFilter($period);
}

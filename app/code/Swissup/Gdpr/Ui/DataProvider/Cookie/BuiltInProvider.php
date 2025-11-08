<?php

namespace Swissup\Gdpr\Ui\DataProvider\Cookie;

use Magento\Ui\DataProvider\AddFieldToCollectionInterface;
use Swissup\Gdpr\Model\ResourceModel\Cookie\BuiltInCollection as Collection;

class BuiltInProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var AddFieldToCollectionInterface[]
     */
    protected $addFieldStrategies;

    /**
     * Construct
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param Collection $collection
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        Collection $collection,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collection;
    }
}

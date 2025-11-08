<?php

namespace Swissup\Gdpr\Ui\DataProvider;

use Magento\Ui\DataProvider\AddFieldToCollectionInterface;
use Swissup\Gdpr\Model\ResourceModel\BlockedCookie\Collection;

class BlockedCookieProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
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

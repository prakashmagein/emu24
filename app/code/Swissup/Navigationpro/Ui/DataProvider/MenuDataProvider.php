<?php

namespace Swissup\Navigationpro\Ui\DataProvider;

class MenuDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * Menu's collection
     *
     * @var \Swissup\Navigationpro\Model\ResourceModel\Menu\Collection
     */
    protected $collection;

    /**
     * @var \Magento\Ui\DataProvider\AddFieldToCollectionInterface[]
     */
    protected $addFieldStrategies;

    /**
     * Construct
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param \Swissup\Navigationpro\Model\ResourceModel\Menu\Collection $collection
     * @param \Magento\Ui\DataProvider\AddFieldToCollectionInterface[] $addFieldStrategies
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Swissup\Navigationpro\Model\ResourceModel\Menu\Collection $collection,
        array $addFieldStrategies = [],
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collection;
        $this->addFieldStrategies = $addFieldStrategies;
    }

    /**
     * Add field to select
     *
     * @param string|array $field
     * @param string|null $alias
     * @return void
     */
    public function addField($field, $alias = null)
    {
        if (isset($this->addFieldStrategies[$field])) {
            $this->addFieldStrategies[$field]->addField($this->getCollection(), $field, $alias);
        } else {
            parent::addField($field, $alias);
        }
    }
}

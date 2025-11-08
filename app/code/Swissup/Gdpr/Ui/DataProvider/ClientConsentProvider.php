<?php

namespace Swissup\Gdpr\Ui\DataProvider;

class ClientConsentProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * ClientConsent collection
     *
     * @var \Swissup\Gdpr\Model\ResourceModel\ClientConsent\Collection
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
     * @param \Swissup\Gdpr\Model\ResourceModel\ClientConsent\Collection $collection
     * @param \Magento\Ui\DataProvider\AddFieldToCollectionInterface[] $addFieldStrategies
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Swissup\Gdpr\Model\ResourceModel\ClientConsent\Collection $collection,
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

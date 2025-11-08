<?php

namespace Swissup\Navigationpro\Ui\DataProvider\Form;

class CreateMenuDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Swissup\Navigationpro\Model\ResourceModel\Menu\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Swissup\Navigationpro\Model\ResourceModel\Menu\CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->collectionFactory = $collectionFactory;
    }

    public function getData()
    {
        return [
            null => [
                'type' => 'simple'
            ]
        ];
    }

    public function getMeta()
    {
        $meta = parent::getMeta();

        $usedIdentifiers = $this->collectionFactory->create()
            ->getColumnValues('identifier');

        $meta['general']['children']['identifier']
            ['arguments']['data']['config']['validation']
            ['navpro-validate-unique'] = $usedIdentifiers;

        return $meta;
    }
}

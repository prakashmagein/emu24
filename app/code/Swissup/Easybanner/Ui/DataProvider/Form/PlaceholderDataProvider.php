<?php

namespace Swissup\Easybanner\Ui\DataProvider\Form;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Framework\App\Request\DataPersistorInterface;
use Swissup\Easybanner\Model\ResourceModel\Placeholder\Collection;
use Swissup\Easybanner\Model\ResourceModel\Placeholder\CollectionFactory;

class PlaceholderDataProvider extends AbstractDataProvider
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $items = $this->collection->getItems();
        /** @var \Swissup\Easybanner\Model\Placeholder $placeholder */
        foreach ($items as $placeholder) {
            $this->loadedData[$placeholder->getId()] = $placeholder->getData();
        }

        $data = $this->dataPersistor->get('easybanner_placeholder');
        if (!empty($data)) {
            $placeholder = $this->collection->getNewEmptyItem();
            $placeholder->setData($data);
            $this->loadedData[$placeholder->getId()] = $placeholder->getData();
            $this->dataPersistor->clear('easybanner_placeholder');
        }

        return $this->loadedData;
    }
}

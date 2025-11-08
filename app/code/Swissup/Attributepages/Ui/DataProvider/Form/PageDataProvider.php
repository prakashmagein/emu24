<?php

namespace Swissup\Attributepages\Ui\DataProvider\Form;

use Swissup\Attributepages\Model\ResourceModel\Entity\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;

class PageDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    private $dataPersistor;

    private $loadedData;

    private $registry;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        \Magento\Framework\Registry $registry,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->registry = $registry;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $items = $this->collection->addAttributeOnlyFilter()->getItems();

        /** @var \Swissup\Attributepages\Model\Page $item */
        foreach ($items as $item) {
            $this->fillMissingValues($item);
            $this->loadedData[$item->getId()] = $item->getData();
        }

        $data = $this->dataPersistor->get('attributepages_page');
        if (!empty($data)) {
            $item = $this->collection->getNewEmptyItem();
            $item->setData($data);
            $this->loadedData[$item->getId()] = array_merge(
                $this->loadedData[$item->getId()] ?? [],
                $item->getData()
            );
            $this->dataPersistor->clear('attributepages_page');
        }

        if (!$this->loadedData) {
            $this->loadedData[null] = $this->getNewItemData();
        }

        return $this->loadedData;
    }

    private function getNewItemData()
    {
        $data = [];
        $item = $this->registry->registry('attributepages_page');

        if ($item) {
            $this->fillMissingValues($item);
            $data = $item->getData();
        }

        return $data;
    }

    private function fillMissingValues($item)
    {
        $item->setAttributeLabel(
            $item->getAttribute()->getFrontendLabel() . ' - ID:' . $item->getAttributeId()
        );

        if (!$item->getIdentifier()) {
            $item->setIdentifier($item->getAttribute()->getAttributeCode());
        }
    }
}

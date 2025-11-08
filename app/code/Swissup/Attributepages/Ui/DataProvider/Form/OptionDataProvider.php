<?php

namespace Swissup\Attributepages\Ui\DataProvider\Form;

use Swissup\Attributepages\Model\ResourceModel\Entity\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;

class OptionDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    private $dataPersistor;

    private $loadedData;

    private $registry;

    private $imageData;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        \Magento\Framework\Registry $registry,
        \Swissup\Attributepages\Model\ImageData $imageData,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->registry = $registry;
        $this->imageData = $imageData;
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

        $items = $this->collection->addOptionOnlyFilter()->getItems();

        /** @var \Swissup\Attributepages\Model\Page $item */
        foreach ($items as $item) {
            $this->fillMissingValues($item);
            $data = $item->getData();

            // prepare image data for ui element
            foreach (['image', 'thumbnail'] as $key) {
                $image = $item->getData($key);
                if ($image && is_string($image)) {
                    $data[$key] = $this->prepareImageData($image);
                }
            }

            $this->loadedData[$item->getId()] = $data;
        }

        $data = $this->dataPersistor->get('attributepages_option');
        if (!empty($data)) {
            $item = $this->collection->getNewEmptyItem();
            $item->setData($data);
            $this->loadedData[$item->getId()] = array_merge(
                $this->loadedData[$item->getId()] ?? [],
                $item->getData()
            );
            $this->dataPersistor->clear('attributepages_option');
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
        if ($item->getParentPage()) {
            $item->setParentPageIdentifier($item->getParentPage()->getIdentifier());
        } else {
            $item->setParentPageIdentifier(__("Looks like the option is excluded from all pages."));
        }
    }

    private function prepareImageData($imageName)
    {
        $url  = $this->imageData->getBaseUrl() . '/' . ltrim($imageName, '/');
        $stat = $this->imageData->getStat($imageName);
        $mime = $this->imageData->getMimeType($imageName);

        return [
            [
                'name' => $imageName,
                'url'  => $url,
                'size' => isset($stat['size']) ? $stat['size'] : 0,
                'type' => $mime,
            ]
        ];
    }
}

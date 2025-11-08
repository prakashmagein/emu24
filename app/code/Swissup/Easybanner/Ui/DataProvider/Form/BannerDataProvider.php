<?php

namespace Swissup\Easybanner\Ui\DataProvider\Form;

use Magento\Framework\App\ObjectManager;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Framework\App\Request\DataPersistorInterface;
use Swissup\Easybanner\Model\ResourceModel\Banner\Collection;
use Swissup\Easybanner\Model\ResourceModel\Banner\CollectionFactory;

class BannerDataProvider extends AbstractDataProvider
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
     * @var \Magento\Catalog\Model\Category\FileInfo|null
     */
    protected $imageData;

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
        \Swissup\Easybanner\Model\Data\Image $imageData,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->imageData = $imageData;
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
        /** @var \Swissup\Easybanner\Model\Banner $banner */
        foreach ($items as $banner) {
            $data = $banner->getData();
            $data['sizes'] = $banner->getSizes();

            // prepare image data for ui element
            $image = $banner->getData('image');
            if ($image && is_string($image)) {
                $data['image'] = $this->prepareImageData($image);
            }

            $this->loadedData[$banner->getId()] = $data;
        }

        $data = $this->dataPersistor->get('easybanner_banner');
        if (!empty($data)) {
            // update image data, if image was just uploaded
            if (isset($data['image'], $data['image'][0]['name'], $data['image'][0]['tmp_name'])) {
                $data['image'] = $this->prepareImageData($data['image'][0]['name']);
            }

            $banner = $this->collection->getNewEmptyItem();
            $banner->setData($data);
            $this->loadedData[$banner->getId()] = $banner->getData();
            $this->dataPersistor->clear('easybanner_banner');
        }

        if (!$this->loadedData) {
            $this->loadedData[null] = [];
        }

        foreach ($this->loadedData as &$data) {
            if (!isset($data['is_responsive'])) {
                $data['is_responsive'] = "1";
            }
            if (empty($data['sizes'])) {
                $data['sizes']['sizes'] = [
                    [
                        'media_query' => '(max-width: 480px)',
                        'image_width' => '480'
                    ],
                ];
            }
        }

        return $this->loadedData;
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

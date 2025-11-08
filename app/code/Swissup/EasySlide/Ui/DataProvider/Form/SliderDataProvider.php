<?php

namespace Swissup\EasySlide\Ui\DataProvider\Form;

use Swissup\EasySlide\Model\ResourceModel\Slider\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;

class SliderDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * Constructor
     *
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
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        /** @var \Swissup\EasySlide\Model\Slider $item */
        foreach ($items as $item) {
            $this->loadedData[$item->getId()] = $item->load($item->getId(), 'slider_id')->getData();
            $isResponsize = true;
            if (empty($item->getResponsiveSizes()) && empty($item->getResponsiveWidths())) {
                $isResponsize = false;
            }

            $this->loadedData[$item->getId()]['slider_config']['is_responsive'] = (string)$isResponsize;
            if (!$isResponsize) {
                // Set default values.
                $this->loadedData[$item->getId()]['slider_config']['responsive_sizes'] = '';
                $this->loadedData[$item->getId()]['slider_config']['responsive_widths'] = '480w, 768w';
            }
        }

        $data = $this->dataPersistor->get('easy_slide_slider');
        if (!empty($data)) {
            $item = $this->collection->getNewEmptyItem();
            $item->setData($data);
            $this->loadedData[$item->getId()] = $item->getData();
            $this->dataPersistor->clear('easy_slide_form');
        }

        if (!$this->loadedData) {
            // new slide default values
            $this->loadedData[null] = [
                'slider_config' => [
                    'speed' => '1000',
                    'autoplay' => '3000',
                    'spaceBetween' => '0',
                    'responsive_sizes' => '',
                    'responsive_widths' => '480w, 768w'
                ]
            ];
        }

        return $this->loadedData;
    }
}

<?php

namespace Swissup\EasySlide\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Data\Collection;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Ui\Component\Form\Element\DataType\Date as UiComponentDate;
use Swissup\EasySlide\Model\ResourceModel\Slides\CollectionFactory;

class Gallery extends AbstractHelper
{
    private $collectionFactory;
    private $imageHelper;
    private $localeDate;
    private $componentDate;

    public function __construct(
        CollectionFactory $collectionFactory,
        Image $imageHelper,
        TimezoneInterface $localeDate,
        UiComponentDate $componentDate,
        Context $context
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->imageHelper = $imageHelper;
        $this->localeDate = $localeDate;
        $this->componentDate = $componentDate;
        parent::__construct($context);
    }

    public function getData($sliderId)
    {
        $collection = $this->collectionFactory->create()
            ->addFieldToFilter('slider_id', $sliderId)
            ->setOrder('sort_order', Collection::SORT_ORDER_ASC);

        $data = [];
        foreach ($collection as $slide) {
            if ($slide->isRawHtml()) {
                $item = [
                    'media_type' => 'html',
                    'slide_id' => $slide->getId(),
                    'position' => $slide->getSortOrder(),
                    'title' => $slide->getTitle(),
                    'description' => $slide->getDescription(),
                    'is_active' => $slide->getIsActive(),
                    'active_from' => $slide->getActiveFromTimestamp(),
                    'active_to' => $slide->getActiveToTimestamp()
                ];
            } else {
                $image = $this->imageHelper;
                $file = $slide->getImage();
                $item = [
                    'media_type' => 'image',
                    'slide_id' => $slide->getId(),
                    'position' => $slide->getSortOrder(),
                    'title' => $slide->getTitle(),
                    'link' => $slide->getUrl(),
                    'target' => $slide->getTarget(),
                    'description' => $slide->getDescription(),
                    'desc_position' => $slide->getDescPosition(),
                    'desc_background' => $slide->getDescBackground(),
                    'is_active' => $slide->getIsActive(),
                    'active_from' => $slide->getActiveFromTimestamp(),
                    'active_to' => $slide->getActiveToTimestamp(),
                    'file' => $file,
                    'url' => $image->getBaseUrl() . $file,
                    'size' => $image->getFileSize($file)
                ];
            }

            $data[] = $item;
        }

        return $data;
    }

    public function getDatepickerConfig()
    {
        $this->componentDate->prepare();
        $config = $this->componentDate->getConfiguration();
        $config['options'] += [
            'showsTime' => true,
            'timeFormat' => $this->localeDate->getTimeFormat()
        ];

        return $config;
    }
}

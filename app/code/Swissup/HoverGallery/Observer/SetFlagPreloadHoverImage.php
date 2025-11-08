<?php

namespace Swissup\HoverGallery\Observer;

use Swissup\HoverGallery\Helper\Data as DataHelper;
use Magento\Framework\Event;

class SetFlagPreloadHoverImage implements Event\ObserverInterface
{
    const FLAG_NAME = 'hovergallery_allow_preload';

    /**
     * @var DataHelper
     */
    private $dataHelper;

    /**
     * @var AppendMediaGalleryBeforeHtml
     */
    private $preloader;

    /**
     * @param DataHelper                   $dataHelper
     * @param AppendMediaGalleryBeforeHtml $dataHelper
     */
    public function __construct(
        DataHelper $dataHelper,
        AppendMediaGalleryBeforeHtml $preloader
    ) {
        $this->dataHelper = $dataHelper;
        $this->preloader = $preloader;
    }

    /**
     * @param  Event\Observer $observer
     * @return void
     */
    public function execute(Event\Observer $observer)
    {
        if (!$this->dataHelper->isEnabled()) {
            return $this;
        }

        $collection = $observer->getCollection();
        $collection->setFlag(self::FLAG_NAME, true);
        if ($collection->isLoaded()) {
            $this->preloader->execute($observer);
        }

    }
}

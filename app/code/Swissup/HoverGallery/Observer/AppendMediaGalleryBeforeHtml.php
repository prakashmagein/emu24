<?php
namespace Swissup\HoverGallery\Observer;

use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Data\Collection;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Model\Product\Gallery\ReadHandler;
use Magento\Catalog\Model\ResourceModel\Product\Gallery;
use Magento\Store\Model\StoreManagerInterface;

class AppendMediaGalleryBeforeHtml implements ObserverInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Gallery
     */
    private $gallery;

    /**
     * @var ReadHandler
     */
    private $readHandler;

    /**
     * @var ProductMetadataInterface
     */
    private $magentoMetadata;

    /**
     * @param StoreManagerInterface $storeManager
     * @param Gallery               $gallery
     * @param ReadHandler           $readHandler
     * @param ProductMetadataInterface $magentoMetadata
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Gallery $gallery,
        ReadHandler $readHandler,
        ProductMetadataInterface $magentoMetadata
    ) {
        $this->storeManager = $storeManager;
        $this->gallery = $gallery;
        $this->readHandler = $readHandler;
        $this->magentoMetadata = $magentoMetadata;
    }

    /**
     * Append media gallery before rendering html
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Swissup\HoverGallery\Observer
     */
    public function execute(Observer $observer)
    {
        $productCollection = $observer->getEvent()->getCollection();
        if ($productCollection instanceof Collection
            && $productCollection->getFlag(SetFlagPreloadHoverImage::FLAG_NAME)
        ) {
            $productIds = $productCollection->getColumnValues('entity_id');
            if (!$productIds) {
                return $this;
            }

            $select = $this->gallery->createBatchBaseSelect(
                $this->storeManager->getStore()->getId(),
                $this->readHandler->getAttribute()->getAttributeId()
            );
            $columnName = $this->getProductIdColumnName();
            $select->where("entity.{$columnName} in (?)", $productIds);
            $galleryData = $this->gallery->getConnection()->fetchAll($select);

            foreach ($productCollection as $product) {
                $media = [];
                foreach ($galleryData as $item) {
                    if ($product->getId() == $item[$columnName]) {
                        $media[] = $item;
                    }
                }
                $this->readHandler->addMediaDataToProduct($product, $media);
                $images = $this->getActiveMediaGalleryEntries($product);

                if ($images
                    && isset($images[1])
                    && $images[1]->getFile() != $product->getImage()
                ) {
                    $product->setHoverImage($images[1]);
                }
            }

            // unset flag
            $productCollection->getFlag(SetFlagPreloadHoverImage::FLAG_NAME, null);
        }

        return $this;
    }

    /**
     * @param  \Magento\Catalog\Model\Product $product
     * @return array
     */
    private function getActiveMediaGalleryEntries($product)
    {
        $images = $product->getMediaGalleryEntries();
        $result = [];

        foreach ($images as $image) {
            if ($image->isDisabled()) {
                continue;
            }
            $result[] = $image;
        }

        return $result;
    }

    /**
     * @return string
     */
    private function getProductIdColumnName()
    {
        return in_array($this->magentoMetadata->getEdition(), ['Enterprise', 'B2B'])
            ? 'row_id'
            : 'entity_id';
    }
}

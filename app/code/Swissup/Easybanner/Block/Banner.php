<?php

namespace Swissup\Easybanner\Block;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;

class Banner extends Template implements BlockInterface, IdentityInterface
{
    /**
     * @var string
     */
    protected $_template = 'banner.phtml';

    /**
     * @var array
     */
    private $images;

    /**
     * @var \Swissup\Easybanner\Model\Banner
     */
    private $banner;

    /**
     * @var \Swissup\Easybanner\Helper\Image
     */
    private $imageHelper;

    /**
     * @var \Swissup\Image\Helper\Dimensions
     */
    private $imageDimensions;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param Template\Context $context
     * @param \Swissup\Easybanner\Model\BannerFactory $bannerFactory
     * @param \Swissup\Easybanner\Helper\Image $imageHelper
     * @param \Swissup\Image\Helper\Dimensions $imageDimensions
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Swissup\Easybanner\Model\BannerFactory $bannerFactory,
        \Swissup\Easybanner\Helper\Image $imageHelper,
        \Swissup\Image\Helper\Dimensions $imageDimensions,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        $this->banner = $bannerFactory->create();
        $this->imageHelper = $imageHelper;
        $this->imageDimensions = $imageDimensions;
        $this->objectManager = $objectManager;

        parent::__construct($context, $data);
    }

    /**
     * @return false|\Swissup\Easybanner\Model\Banner
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBannerData()
    {
        if ($this->getBannerObject()) {
            $this->banner = $this->getBannerObject();
        } else {
            $bannerId = $this->getBanner();
            if (!$bannerId) {
                return false;
            }
            $this->banner->load($bannerId);

            // do not render popup banners twice when they added in _Content > Widgets_
            if ($this->banner->isPopupType()) {
                return false;
            }
        }

        if (($this->hasStoreId() && !$this->getStoreId()) ||
            ($this->banner->hasStoreId() && !$this->banner->getStoreId())
        ) {
            return $this->banner;
        }

        if (!$this->banner->getId() || !$this->banner->getStatus()) {
            return false;
        }

        $storeId = $this->_storeManager->getStore()->getId();
        if (!$this->banner->isVisible($storeId)) {
            return false;
        }

        if ($this->banner->getIsTrackable()) {
            $statistic = $this->objectManager->create('Swissup\Easybanner\Model\BannerStatistic');
            $statistic->incrementDisplayCount($this->banner->getId());
        }

        return $this->banner;
    }

    public function getBannerUrl()
    {
        $url = 'easybanner/click/index/id/' . $this->banner->getId();
        if (!$this->banner->getHideUrl()) {
            $url .= '/url/' . trim($this->banner->getUrl(), '/');
        }

        return $url;
    }

    public function getBannerImage()
    {
        if (!$image = $this->banner->getImage()) {
            return false;
        }

        $mediaUrl = $this->_storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        return $mediaUrl . 'easybanner/' . ltrim($image, '/');
    }

    /**
     * @return int|float
     */
    public function calculateWidth()
    {
        if (!$this->getBannerImage()) {
            return 0;
        }

        return $this->imageDimensions->getWidth($this->getBannerImage());
    }

    /**
     * @return int|float
     */
    public function calculateHeight()
    {
        if (!$this->getBannerImage()) {
            return 0;
        }

        return $this->imageDimensions->getHeight($this->getBannerImage());
    }

    public function resizeImage($width, $height)
    {
        $key = $width . '_' . $height;

        if (!isset($this->images[$key])) {
            $this->images[$key] = $this->imageHelper->resize($this->banner, $width, $height);
        }

        return $this->images[$key];
    }

    public function getBannerHtml()
    {
        $bannerHtml = $this->banner->getHtml();
        $bannerHtml = str_replace('{{tm_banner_url}}', $this->getUrl($this->getBannerUrl()), $bannerHtml);
        $bannerHtml = str_replace('{{swissup_easybanner_url}}', $this->getUrl($this->getBannerUrl()), $bannerHtml);
        $cmsFilter = $this->objectManager->get('Magento\Cms\Model\Template\FilterProvider');
        $storeId = $this->_storeManager->getStore()->getId();
        $html = $cmsFilter->getBlockFilter()
            ->setStoreId($storeId)
            ->filter($bannerHtml);

        return $html;
    }

    public function getSystemClassName()
    {
        $class = 'easybanner-banner';

        if ($this->banner->isPopupType()) {
            $class .= ' placeholder-' . $this->banner->getTypeCode();
        }

        return $class;
    }

    public function getClassName()
    {
        $class = $this->getSystemClassName();

        $class .= ' ' . $this->banner->getHtmlId();

        if ($this->banner->getClassName()) {
            $class .= ' ' . $this->banner->getClassName();
        }

        if ($this->banner->getAdditionalCssClass()) {
            $class .= ' ' . $this->banner->getAdditionalCssClass();
        }

        if ($this->getAdditionalCssClass()) {
            $class .= ' ' . $this->getAdditionalCssClass();
        }

        return $class;
    }

    /**
     * @return string
     */
    public function getImageSrcset()
    {
        if (!$this->getData('image_srcset')) {
            $this->prepareResponsiveImageAttributes();
        }
        return $this->getData('image_srcset');
    }

    /**
     * @return string
     */
    public function getImageSizes()
    {
        if (!$this->getData('image_sizes')) {
            $this->prepareResponsiveImageAttributes();
        }
        return $this->getData('image_sizes');
    }

    protected function prepareResponsiveImageAttributes()
    {
        $originalWidth  = $this->banner->getWidth() ?: $this->calculateWidth();
        $originalHeight = $this->banner->getHeight() ?: $this->calculateHeight();
        $ratio = $originalHeight / $originalWidth;
        $sizes = $this->banner->getSizes();

        if (empty($sizes['sizes'])) {
            return;
        }

        $srcsetAttr = [];
        $sizesAttr = [];
        foreach ($sizes['sizes'] as $item) {
            $width = str_replace('px', '', $item['image_width']); // Remove 'px' just in case...
            $query = $item['media_query'];
            if (!$width || $width >= $originalWidth) {
                continue;
            }

            $height = ceil($width * $ratio);

            $srcsetAttr[] = "{$this->resizeImage($width, $height)} {$width}w";
            $sizesAttr[] = "{$query} {$width}px";
        }

        $srcsetAttr[] = "{$this->resizeImage($originalWidth, $originalHeight)} {$originalWidth}w";
        $sizesAttr[] = "{$originalWidth}px";

        $this->setData('image_srcset', implode(', ', $srcsetAttr));
        $this->setData('image_sizes', implode(', ', $sizesAttr));
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        if ($this->getBannerObject()) {
            $bannerId = $this->getBannerObject()->getBannerId();
        } else {
            $bannerId = $this->getBanner();
        }

        return [\Swissup\Easybanner\Model\Banner::CACHE_TAG . '_' . $bannerId];
    }
}

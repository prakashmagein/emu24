<?php
namespace Swissup\HoverGallery\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Catalog\Block\Product\ImageBuilder;

class Data extends AbstractHelper
{
    /**
     * @var ImageBuilder
     */
    private $imageBuilder;

    /**
     * @var \Magento\Framework\View\ConfigInterface
     */
    private $presentationConfig;

    /**
     * Path to store config if frontend output is enabled
     *
     * @var string
     */
    const XML_PATH_ENABLED = 'hovergallery/general/enabled';

    /**
     * @param Context                                 $context
     * @param ImageBuilder                            $imageBuilder
     * @param \Magento\Framework\View\ConfigInterface $presentationConfig
     */
    public function __construct(
        Context $context,
        ImageBuilder $imageBuilder,
        \Magento\Framework\View\ConfigInterface $presentationConfig
    ) {
        $this->imageBuilder = $imageBuilder;
        $this->presentationConfig = $presentationConfig;
        parent::__construct($context);
    }

    /**
     * @param  string $key
     * @return string
     */
    protected function getConfig($key)
    {
        return $this->scopeConfig->getValue($key, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Checks whether hover gallery can be displayed in the frontend
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return (bool)$this->getConfig(self::XML_PATH_ENABLED);
    }

    /**
     * Get product hover image html
     * @param \Magento\Catalog\Model\Product $product
     * @param string $width
     * @param string $height
     * @param bool $keepFrame
     * @return string
     * @deprecated 1.3.0 Use renderHoverImage
     */
    public function getHoverImage($product, $width, $height, $keepFrame = true)
    {
        return '';
    }

    /**
     * Render HTML of hover image for product
     *
     * @param  \Magento\Catalog\Model\Product $product
     * @param  string $imageId
     * @param  string $template
     * @return string
     */
    public function renderHoverImage(
        \Magento\Catalog\Model\Product $product,
        $imageId = 'category_page_grid',
        $template = 'Swissup_HoverGallery::product/hover-image.phtml'
    ) {
        $imageData = $product->getHoverImage();
        if (!$imageData) {
            return '';
        }

        $viewImageConfig = $this->getViewConfig($imageId);
        if (!is_array($viewImageConfig)
            || !isset($viewImageConfig['type'])
        ) {
            return '';
        }

        $imageType = $viewImageConfig['type'];
        $clonedProduct = clone $product;
        // I clonod $product because I need to change main image and don't want
        // to affect original object.
        $clonedProduct->setData($imageType, $imageData['file']);
        $this->imageBuilder
            ->setProduct($clonedProduct)
            ->setImageId($imageId);

        return $this->imageBuilder->create()
            ->setTemplate($template)
            ->setData('prolabels_memoization_key', 'no_prolabels')
            ->toHtml();
    }

    /**
     * @param  string $imageId
     * @return array|null
     */
    public function getViewConfig($imageId)
    {
        return $this->presentationConfig->getViewConfig()->getMediaAttributes(
            'Magento_Catalog',
            \Magento\Catalog\Helper\Image::MEDIA_TYPE_CONFIG_NODE,
            $imageId
        );
    }
}

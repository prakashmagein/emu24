<?php

namespace Swissup\SoldTogether\Block\Product\Renderer\Listing;

use Magento\Framework\App\ObjectManager;
use Magento\Catalog\Model\Product\Image\UrlBuilder;
use Magento\Swatches\Block\Product\Renderer\Configurable as SwatchesConfigurable;

class Configurable extends \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable
{
    /**
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        $this->addChild(
            'priceConfig',
            \Swissup\SoldTogether\Block\Product\Renderer\PriceConfig::class
        );

        return parent::_prepareLayout();
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheKey()
    {
        return parent::getCacheKey() . '-' . $this->getProduct()->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function getProduct()
    {
        return $this->hasData('product') ?
            $this->getData('product') :
            new \Magento\Framework\DataObject(['identities' => []]);
    }

    /**
     * Get configuration for the price
     *
     * @return string
     */
    public function getPriceConfigJson()
    {
        $product = $this->getProduct();
        $priceConfig = $this->getChildBlock('priceConfig');
        $priceConfig->setProduct($product);

        return $priceConfig->getJsonConfig();
    }

    /**
     * {@inheritdoc}
     */
    public function getJsonConfig()
    {
        $this->unsAllowProducts();

        return parent::getJsonConfig();
    }

    /**
     * {@inheritdoc}
     */
    protected function getOptionImages()
    {
        $images = [];
        /**
         * Don't generate option images. It is bad for overall page performance.
         * Change image in javascript with request to swatches controller.
         */
        // $imageId = $this->getData('image_id') ?: 'related_products_list';
        // $urlBuilder = ObjectManager::getInstance()->get(UrlBuilder::class);
        // foreach ($this->getAllowProducts() as $product) {
        //     $productImages = $product->getMediaGalleryImages();
        //     foreach ($productImages as $image) {
        //         $images[$product->getId()][] = [
        //             'img' => $urlBuilder->getUrl($image->getFile(), $imageId)
        //         ];
        //     }
        // }

        return $images;
    }

    /**
     * Build html ID for dropdown element
     * (It can have only letters and digits. Digits are an attribute Id!!!)
     *
     * @param  [type] $attribute
     * @return string
     */
    public function getDropdownHtmlId($attribute)
    {
        $digits = range('0', '9');
        $alphabet = range('a', 'j');
        $product = $this->getProduct();

        return str_replace($digits, $alphabet, $product->getId())
            . $attribute->getAttributeId();
    }

    public function getMediaCallbackUrl()
    {
        if ($this->getModuleManager()->isEnabled('Magento_Swatches')) {
            if ($callbackUrl = SwatchesConfigurable::MEDIA_CALLBACK_ACTION) {
                return $this->getUrl(
                    $callbackUrl,
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
        }

        return null;
    }

    private function getModuleManager() {
        return ObjectManager::getInstance()
            ->get('Magento\Framework\Module\Manager');
    }
}

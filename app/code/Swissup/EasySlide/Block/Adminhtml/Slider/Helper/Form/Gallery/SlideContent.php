<?php
/**
 * Slide form gallery content
 *
 * @method \Magento\Framework\Data\Form\Element\AbstractElement getElement()
 */
namespace Swissup\EasySlide\Block\Adminhtml\Slider\Helper\Form\Gallery;

use Magento\Framework\App\ObjectManager;
use Swissup\EasySlide\Model\DataProviders\ImageUploadConfig as ImageUploadConfigDataProvider;
use Swissup\EasySlide\Model\Config\Source\DescriptionBackground;
use Swissup\EasySlide\Model\Config\Source\DescriptionPosition;

class SlideContent extends \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Gallery\Content
{
    /**
     * {@inheritdoc}
     */
    protected $_template = 'helper/gallery.phtml';

    /**
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        $imageUploadConfigDataProvider = ObjectManager::getInstance()
            ->get(ImageUploadConfigDataProvider::class);
        $this->addChild(
            'uploader',
            \Magento\Backend\Block\Media\Uploader::class,
            [
                'image_upload_config_data' => $imageUploadConfigDataProvider
            ]
        );
        $url = $this->_urlBuilder->getUrl('easyslide/slider/upload');
        $this->getUploader()->getConfig()->setUrl(
            $url
        )->setFileField(
            'image'
        )->setFilters(
            [
                'images' => [
                    'label' => __('Images (.gif, .jpg, .png)'),
                    'files' => ['*.gif', '*.jpg', '*.jpeg', '*.png'],
                ],
            ]
        );

        return $this;
    }

    public function getMediaAttributes()
    {
        return [];
    }

    public function getDescPosValues()
    {
        return ObjectManager::getInstance()
            ->create(DescriptionPosition::class)
            ->toOptionArray();
    }

    public function getDescBackValues()
    {
        return ObjectManager::getInstance()
            ->create(DescriptionBackground::class)
            ->toOptionArray();
    }

    public function getTargetValues()
    {
        return [
            "0" => ["value" => "_self", "label" => "Same window"],
            "1" => ["value" => "_blank", "label" => "New window"]
        ];
    }

    public function getActiveValues()
    {
        return [
            "0" => ["value" => 0, "label" => "No"],
            "1" => ["value" => 1, "label" => "Yes"]
        ];
    }
}

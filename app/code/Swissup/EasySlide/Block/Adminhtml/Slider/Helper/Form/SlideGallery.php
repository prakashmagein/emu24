<?php
/**
 * Slide gallery attribute
 */
namespace Swissup\EasySlide\Block\Adminhtml\Slider\Helper\Form;

use Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Gallery as ProductGallery;

class SlideGallery extends ProductGallery
{
    /**
     * {@inheritdoc}
     */
    protected $formName = 'easyslide_slider_form';

    /**
     * {@inheritdoc}
     */
    public function getContentHtml()
    {
        $slideContent = $this->_layout
            ->createBlock(Gallery\SlideContent::class, 'swissup.easyslide.slideContent')
            ->setElement($this);
        $slideContent->setId($this->getHtmlId() . '_content')->setElement($this);
        $slideContent->setFormName($this->formName);
        $gallery = $slideContent->getJsObjectName();
        $slideContent->getUploader()->getConfig()->setMegiaGallery($gallery);
        return $slideContent->toHtml();
    }
}

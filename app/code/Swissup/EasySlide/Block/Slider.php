<?php

namespace Swissup\EasySlide\Block;

use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;
use Magento\Framework\DataObject\IdentityInterface;

class Slider extends Template implements BlockInterface, IdentityInterface
{
    /**
     * @var string
     */
    protected $_template = 'slider.phtml';

    /**
     * @var \Swissup\EasySlide\Model\SliderFactory
     */
    protected $sliderFactory;

    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $filterProvider;

    /**
     * @var \Swissup\EasySlide\Model\Slider
     */
    protected $slider = null;

    /**
     * @var Image
     */
    protected $imageRenderer;

    /**
     * @param Template\Context                           $context
     * @param \Swissup\EasySlide\Model\SliderFactory     $sliderFactory
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     * @param Image                                      $imageRenderer
     * @param array                                      $data
     */
    public function __construct(
        Template\Context $context,
        \Swissup\EasySlide\Model\SliderFactory $sliderFactory,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        Image $imageRenderer,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->sliderFactory = $sliderFactory;
        $this->filterProvider = $filterProvider;
        $this->imageRenderer = $imageRenderer;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentities() {
        $slider = $this->getSlider();

        return $slider ? $slider->getIdentities() : [];
    }

    /**
     * Get slider model
     *
     * @return \Swissup\EasySlide\Model\Slider|boolean
     */
    public function getSlider()
    {
        $identifier = $this->getIdentifier();
        if (!$identifier) {
            return false;
        }

        if (!$this->slider) {
            $this->slider = $this->sliderFactory->create()
                ->load($identifier, 'identifier');
        }

        return $this->slider->getIsActive() ? $this->slider : false;
    }

    /**
     * Get processed slide description
     *
     * @param  string $description
     * @return string
     */
    public function getSlideDescription($description)
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $result = $this->filterProvider->getBlockFilter()
            ->setStoreId($storeId)
            ->filter($description);

        return $result;
    }

    /**
     * Get JSON slider config for mage-init
     *
     * @return string
     */
    public function getSliderConfig()
    {
        if (!$slider = $this->getSlider()) {
            return '{}';
        }

        $jsConfig = $slider->getJsConfig();

        return $jsConfig->toJson();
    }

    /**
     * Get slide image renderer
     *
     * @return Image
     */
    public function getImageRenderer()
    {
        return $this->imageRenderer;
    }
}

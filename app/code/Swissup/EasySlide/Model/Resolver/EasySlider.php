<?php
declare(strict_types=1);

namespace Swissup\EasySlide\Model\Resolver;

use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Widget\Model\Template\FilterEmulate;

class EasySlider implements ResolverInterface
{
    /**
     * @var \Swissup\EasySlide\Model\SliderFactory
     */
    private $sliderFactory;

    /**
     * @var \Swissup\EasySlide\Helper\Image
     */
    private $imageHelper;

    /**
     * @var FilterEmulate
     */
    private $widgetFilter;

    /**
     * @param \Swissup\EasySlide\Model\SliderFactory $sliderFactory
     * @param \Swissup\EasySlide\Helper\Image $imageHelper
     * @param FilterEmulate $widgetFilter
     */
    public function __construct(
        \Swissup\EasySlide\Model\SliderFactory $sliderFactory,
        \Swissup\EasySlide\Helper\Image $imageHelper,
        FilterEmulate $widgetFilter
    ) {
        $this->sliderFactory = $sliderFactory;
        $this->imageHelper = $imageHelper;
        $this->widgetFilter = $widgetFilter;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ) {
        if (!isset($args['identifier'])) {
            throw new GraphQlInputException(
                __("'identifier' input argument is required.")
            );
        }

        $identifier = $args['identifier'];

        /* @var \Swissup\EasySlide\Model\Slider $slider */
        $slider = $this->sliderFactory->create()
                ->load($identifier, 'identifier');

        $data = $slider->getData();

        $config = $slider->getSliderConfig();
        if (isset($data['sizes'])) {
            $config['sizes'] = isset($data['sizes']['sizes']) ? $data['sizes']['sizes'] : [];
            unset($data['sizes']);
        }

        $propertyConverting = [
            'bool' => [
                'freeMode',
                'loop',
                'lazy',
                'scrollbar',
                'pagination',
                'navigation'
            ],
            'int' => [
                'speed',
                'autoplay',
                'spaceBetween',
                'slidesPerView',
                'startRandomSlide',
                'loadPrevNext'
            ],
            'string' => [
                'thumbs',
                'thumb_width',
                'thumb_height',
                'thumbs_position',
                'theme',
                'effect'
            ]
        ];
        foreach ($propertyConverting['bool'] as $property) {
            if (isset($config[$property])) {
                $config[$property] = (bool) $config[$property];
            }
        }
        foreach ($propertyConverting['string'] as $property) {
            if (isset($config[$property])) {
                $config[$property] = (string) $config[$property];
            }
        }
        foreach ($propertyConverting['int'] as $property) {
            if (isset($config[$property])) {
                $config[$property] = (int) $config[$property];
            }
        }
        $data['slider_config'] = $config;

        $slides = $slider->getSlides();
        foreach ($slides as &$slide) {
//            $slide['url'] = $this->imageHelper->getBaseUrl() . $slide['image'];
            $slide['width'] = $this->imageHelper->getImageWidth($slide['image']);
            $slide['height'] = $this->imageHelper->getImageHeight($slide['image']);
            if (!empty($slide['description'])) {
                $slide['description'] = $this->widgetFilter->filter($slide['description']);
            }
        }
        $data['slides'] = $slides;

        return $data;
    }
}

<?php

namespace Swissup\EasySlide\Installer\Command;

class Slider
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Swissup\EasySlide\Model\SliderFactory
     */
    private $sliderFactory;

    /**
     * @var \Swissup\EasySlide\Model\SlidesFactory
     */
    private $slidesFactory;

    /**
     * @param \Swissup\EasySlide\Model\SliderFactory $sliderFactory
     * @param \Swissup\EasySlide\Model\SlidesFactory $slidesFactory
     */
    public function __construct(
        \Swissup\EasySlide\Model\SliderFactory $sliderFactory,
        \Swissup\EasySlide\Model\SlidesFactory $slidesFactory
    ) {
        $this->sliderFactory = $sliderFactory;
        $this->slidesFactory = $slidesFactory;
    }

    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    private function getSliderDefaults()
    {
        return [
            'is_active' => 1,
            'speed' => 1000,
            'pagination' => 1,
            'navigation' => 0,
            'scrollbar' => 0,
            'autoplay' => 3000,
            'effect' => 'slide',
            'lazy' => 1,
            'theme' => '',
            'loadPrevNext' => 0,
        ];
    }

    private function getSlideDefaults()
    {
        return [
            'is_active' => 1,
            'target' => '_self',
            'title' => '',
            'description' => '',
            'link' => '',
        ];
    }

    /**
     * Create new slider.
     * If duplicate is found - do nothing.
     *
     * @param \Swissup\Marketplace\Installer\Request $request
     */
    public function execute($request)
    {
        $this->logger->info('Easyslider: Create sliders');
        $sliderDefaults = $this->getSliderDefaults();
        foreach ($request->getParams() as $data) {
            $slider = $this->sliderFactory
                ->create()
                ->load($data['identifier'], 'identifier');

            if ($slider->getId()) {
                continue;
            }

            try {
                $slider->setData(array_merge($sliderDefaults, $data))->save();
            } catch (\Exception $e) {
                $this->logger->warning($e->getMessage());
                continue;
            }

            if (empty($data['slides'])) {
                continue;
            }

            $slideDefaults = array_merge($this->getSlideDefaults(), [
                'slider_id' => $slider->getId(),
            ]);

            foreach ($data['slides'] as $slide) {
                try {
                    $this->slidesFactory
                        ->create()
                        ->setData(array_merge($slideDefaults, $slide))
                        ->save();
                } catch (\Exception $e) {
                    $this->logger->warning($e->getMessage());
                }
            }
        }
    }
}

<?php
namespace Swissup\EasySlide\Api\Data;

interface SliderInterface
{
    const SLIDER_ID = 'slider_id';
    const IDENTIFIER = 'identifier';
    const TITLE = 'title';
    const SLIDER_CONFIG = 'slider_config';
    const IS_ACTIVE = 'is_active';
    const SIZES = 'sizes/sizes';

    /**
     * Get slider_id
     *
     * return int
     */
    public function getSliderId();

    /**
     * Get identifier
     *
     * return string
     */
    public function getIdentifier();

    /**
     * Get title
     *
     * return string
     */
    public function getTitle();

    /**
     * Get slider_config
     *
     * return string
     */
    public function getSliderConfig();

    /**
     * Get is_active
     *
     * return int
     */
    public function getIsActive();


    /**
     * Set slider_id
     *
     * @param int $sliderId
     * return \Swissup\Easyslide\Api\Data\SliderInterface
     */
    public function setSliderId($sliderId);

    /**
     * Set identifier
     *
     * @param string $identifier
     * return \Swissup\Easyslide\Api\Data\SliderInterface
     */
    public function setIdentifier($identifier);

    /**
     * Set title
     *
     * @param string $title
     * return \Swissup\Easyslide\Api\Data\SliderInterface
     */
    public function setTitle($title);

    /**
     * Set slider_config
     *
     * @param string $sliderConfig
     * return \Swissup\Easyslide\Api\Data\SliderInterface
     */
    public function setSliderConfig($sliderConfig);

    /**
     * Set is_active
     *
     * @param int $isActive
     * return \Swissup\Easyslide\Api\Data\SliderInterface
     */
    public function setIsActive($isActive);
}

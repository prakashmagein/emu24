<?php
namespace Swissup\Easybanner\Api\Data;

interface BannerInterface
{
    const BANNER_ID = 'banner_id';
    const IDENTIFIER = 'identifier';
    const SORT_ORDER = 'sort_order';
    const TITLE = 'title';
    const URL = 'url';
    const IMAGE = 'image';
    const HTML = 'html';
    const STATUS = 'status';
    const MODE = 'mode';
    const TARGET = 'target';
    const HIDE_URL = 'hide_url';
    const CONDITIONS_SERIALIZED = 'conditions_serialized';
    const RESIZE_IMAGE = 'resize_image';
    const WIDTH = 'width';
    const HEIGHT = 'height';
    const RETINA_SUPPORT = 'retina_support';
    const BACKGROUND_COLOR = 'background_color';
    const CLASS_NAME = 'class_name';

    /**
     * Get banner_id
     *
     * @return int
     */
    public function getBannerId();

    /**
     * Get identifier
     *
     * @return string
     */
    public function getIdentifier();

    /**
     * Get sort_order
     *
     * @return int
     */
    public function getSortOrder();

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle();

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl();

    /**
     * Get image
     *
     * @return string
     */
    public function getImage();

    /**
     * Get html
     *
     * @return string
     */
    public function getHtml();

    /**
     * Get status
     *
     * @return int
     */
    public function getStatus();

    /**
     * Get mode
     *
     * @return string
     */
    public function getMode();

    /**
     * Get target
     *
     * @return string
     */
    public function getTarget();

    /**
     * Get hide_url
     *
     * @return int
     */
    public function getHideUrl();

    /**
     * Get conditions_serialized
     *
     * @return string
     */
    public function getConditionsSerialized();

    /**
     * Get resize_image
     *
     * @return int
     */
    public function getResizeImage();

    /**
     * Get width
     *
     * @return int
     */
    public function getWidth();

    /**
     * Get height
     *
     * @return int
     */
    public function getHeight();

    /**
     * Get retina_support
     *
     * @return int
     * @deprecated 1.6.0
     */
    public function getRetinaSupport();

    /**
     * Get background_color
     *
     * @return string
     */
    public function getBackgroundColor();

    /**
     * Get class_name
     *
     * @return string
     */
    public function getClassName();


    /**
     * Set banner_id
     *
     * @param int $bannerId
     * @return \Swissup\Easybanner\Api\Data\BannerInterface
     */
    public function setBannerId($bannerId);

    /**
     * Set identifier
     *
     * @param string $identifier
     * @return \Swissup\Easybanner\Api\Data\BannerInterface
     */
    public function setIdentifier($identifier);

    /**
     * Set sort_order
     *
     * @param int $sortOrder
     * @return \Swissup\Easybanner\Api\Data\BannerInterface
     */
    public function setSortOrder($sortOrder);

    /**
     * Set title
     *
     * @param string $title
     * @return \Swissup\Easybanner\Api\Data\BannerInterface
     */
    public function setTitle($title);

    /**
     * Set url
     *
     * @param string $url
     * @return \Swissup\Easybanner\Api\Data\BannerInterface
     */
    public function setUrl($url);

    /**
     * Set image
     *
     * @param string $image
     * @return \Swissup\Easybanner\Api\Data\BannerInterface
     */
    public function setImage($image);

    /**
     * Set html
     *
     * @param string $html
     * @return \Swissup\Easybanner\Api\Data\BannerInterface
     */
    public function setHtml($html);

    /**
     * Set status
     *
     * @param int $status
     * @return \Swissup\Easybanner\Api\Data\BannerInterface
     */
    public function setStatus($status);

    /**
     * Set mode
     *
     * @param string $mode
     * @return \Swissup\Easybanner\Api\Data\BannerInterface
     */
    public function setMode($mode);

    /**
     * Set target
     *
     * @param string $target
     * @return \Swissup\Easybanner\Api\Data\BannerInterface
     */
    public function setTarget($target);

    /**
     * Set hide_url
     *
     * @param int $hideUrl
     * @return \Swissup\Easybanner\Api\Data\BannerInterface
     */
    public function setHideUrl($hideUrl);

    /**
     * Set conditions_serialized
     *
     * @param string $conditionsSerialized
     * @return \Swissup\Easybanner\Api\Data\BannerInterface
     */
    public function setConditionsSerialized($conditionsSerialized);

    /**
     * Set resize_image
     *
     * @param int $resizeImage
     * @return \Swissup\Easybanner\Api\Data\BannerInterface
     */
    public function setResizeImage($resizeImage);

    /**
     * Set width
     *
     * @param int $width
     * @return \Swissup\Easybanner\Api\Data\BannerInterface
     */
    public function setWidth($width);

    /**
     * Set height
     *
     * @param int $height
     * @return \Swissup\Easybanner\Api\Data\BannerInterface
     */
    public function setHeight($height);

    /**
     * Set retina_support
     *
     * @param int $retinaSupport
     * @return \Swissup\Easybanner\Api\Data\BannerInterface
     */
    public function setRetinaSupport($retinaSupport);

    /**
     * Set background_color
     *
     * @param string $backgroundColor
     * @return \Swissup\Easybanner\Api\Data\BannerInterface
     */
    public function setBackgroundColor($backgroundColor);

    /**
     * Set class_name
     *
     * @param string $className
     * @return \Swissup\Easybanner\Api\Data\BannerInterface
     */
    public function setClassName($className);
}

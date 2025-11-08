<?php

namespace Swissup\Navigationpro\Model;

use Magento\Framework\Model\AbstractModel;
use Swissup\Navigationpro\Api\Data\EntityInterface;

abstract class AbstractEntity extends AbstractModel
{
    const STATUS_ENABLED  = 1;
    const STATUS_DISABLED = 0;

    const DROPDOWN_WIDTH_FULLSCREEN = 'fullscreen';
    const DROPDOWN_WIDTH_FULLWIDTH  = 'fullwidth';
    const DROPDOWN_WIDTH_BOXED      = 'boxed';
    const DROPDOWN_WIDTH_XLARGE     = 'xlarge';
    const DROPDOWN_WIDTH_LARGE      = 'large';
    const DROPDOWN_WIDTH_MEDIUM     = 'medium';
    const DROPDOWN_WIDTH_SMALL      = 'small';

    const DROPDOWN_SIDE_RIGHT       = '';
    const DROPDOWN_SIDE_LEFT        = 'left';
    const DROPDOWN_SIDE_TOP         = 'top';

    const DROPDOWN_POSITION_LEFT   = 'left';
    const DROPDOWN_POSITION_CENTER = 'center';
    const DROPDOWN_POSITION_RIGHT  = 'right';

    /**
     * Prepare dropdown positions
     *
     * @return array
     */
    public function getAvailableDropdownPositions()
    {
        return [
            self::DROPDOWN_POSITION_LEFT    => __('Stick to Left'),
            self::DROPDOWN_POSITION_CENTER  => __('Center'),
            self::DROPDOWN_POSITION_RIGHT   => __('Stick to Right'),
        ];
    }

    /**
     * Prepare dropdown positioning sides
     *
     * @return array
     */
    public function getAvailableDropdownSides()
    {
        return [
            self::DROPDOWN_SIDE_RIGHT => __('Standard'),
            self::DROPDOWN_SIDE_LEFT  => __('Left'),
            self::DROPDOWN_SIDE_TOP   => __('Top'),
        ];
    }

    /**
     * Prepare menu statuses
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [
            self::STATUS_ENABLED  => __('Enabled'),
            self::STATUS_DISABLED => __('Disabled')
        ];
    }

    /**
     * Prepare menu width modes
     *
     * @return array
     */
    public function getAvailableDropdownWidths()
    {
        return [
            self::DROPDOWN_WIDTH_FULLSCREEN => __('Fullscreen'),
            self::DROPDOWN_WIDTH_FULLWIDTH  => __('Full-Width'),
            self::DROPDOWN_WIDTH_BOXED      => __('Boxed'),
            self::DROPDOWN_WIDTH_SMALL      => __('Small'),
            self::DROPDOWN_WIDTH_MEDIUM     => __('Medium'),
            self::DROPDOWN_WIDTH_LARGE      => __('Large'),
            self::DROPDOWN_WIDTH_XLARGE     => __('Extra Large'),
        ];
    }

    /**
     * Is active
     *
     * @return bool
     */
    public function isActive()
    {
        return self::STATUS_ENABLED === $this->getIsActive();
    }

    /**
     * 1. Fixed cache cleanup when FPC is disabled
     * 2. Fixed cache cleanup when saving menu item in backend
     *
     * @return array|false
     */
    public function getCacheTags()
    {
        $tags = parent::getCacheTags() ?? [];
        $tags = array_merge($tags, $this->getIdentities());
        return $tags;
    }
}

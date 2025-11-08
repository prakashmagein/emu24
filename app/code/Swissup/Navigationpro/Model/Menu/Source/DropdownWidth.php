<?php

namespace Swissup\Navigationpro\Model\Menu\Source;

use Swissup\Navigationpro\Model\Menu;
use Magento\Framework\Data\OptionSourceInterface;

class DropdownWidth implements OptionSourceInterface
{
    /**
     * @var \Swissup\Navigationpro\Model\Menu
     */
    protected $menu;

    /**
     * Constructor
     *
     * @param \Swissup\Navigationpro\Model\Menu $menu
     */
    public function __construct(\Swissup\Navigationpro\Model\Menu $menu)
    {
        $this->menu = $menu;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $availableWidths = $this->menu->getAvailableDropdownWidths();
        $options = [];
        foreach ($availableWidths as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
                'for_first_level_only' => (int) in_array(
                    $key,
                    [
                        Menu::DROPDOWN_WIDTH_FULLSCREEN,
                        Menu::DROPDOWN_WIDTH_FULLWIDTH,
                        Menu::DROPDOWN_WIDTH_BOXED,
                    ]
                )
            ];
        }
        return $options;
    }
}

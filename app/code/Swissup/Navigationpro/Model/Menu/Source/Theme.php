<?php

namespace Swissup\Navigationpro\Model\Menu\Source;

class Theme implements \Magento\Framework\Option\ArrayInterface
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
        $availableThemes = $this->menu->getAvailableThemes();
        $options = [];
        foreach ($availableThemes as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}

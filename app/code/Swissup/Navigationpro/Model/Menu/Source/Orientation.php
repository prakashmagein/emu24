<?php

namespace Swissup\Navigationpro\Model\Menu\Source;

class Orientation implements \Magento\Framework\Option\ArrayInterface
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
        $availableOrientations = $this->menu->getAvailableOrientations();
        $options = [];
        foreach ($availableOrientations as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}

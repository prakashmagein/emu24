<?php

namespace Swissup\Navigationpro\Model\Menu\Builder;

class Sidebar extends \Swissup\Navigationpro\Model\Menu\Builder
{
    protected function prepareSettings()
    {
        return $this->setSettings([
            'max_depth' => 0,
            'identifier' => 'sidebar',
        ]);
    }

    protected function prepareItems()
    {
        return $this->setItems([
            'categories' => [
                'method' => 'importCategories'
            ],
        ]);
    }

    protected function prepareWidgetSettings()
    {
        return $this->setWidgetSettings([
            'title' => '',
            'sort_order' => 0,
            'page_groups' => [
                [
                    'page_group' => 'all_pages',
                    'all_pages' => [
                        'page_id' => '0',
                        'for' => 'all',
                        'layout_handle' => 'default',
                        'block' => 'sidebar.main.top',
                    ],
                    'page_layouts' => [
                        'layout_handle' => '',
                    ],
                ],
            ],
            'params' => [
                'visible_levels'     => 2,
                'show_active_branch' => 0,
                'theme'              => 'flat',
                'orientation'        => 'vertical',
                'wrap'               => 1,
                'block_title'        => 'Categories',
            ],
        ]);
    }
}

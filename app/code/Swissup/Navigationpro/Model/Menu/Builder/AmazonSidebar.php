<?php

namespace Swissup\Navigationpro\Model\Menu\Builder;

class AmazonSidebar extends \Swissup\Navigationpro\Model\Menu\Builder
{
    protected function prepareSettings()
    {
        return $this->setSettings([
            'max_depth' => 0,
            'identifier' => 'amazon_sidebar',
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
                    'page_group' => 'pages',
                    'pages' => [
                        'page_id' => '0',
                        'for' => 'all',
                        'layout_handle' => 'catalog_category_view',
                        'block' => 'sidebar.main.top',
                    ],
                    'page_layouts' => [
                        'layout_handle' => '',
                    ],
                ],
            ],
            'params' => [
                'show_active_branch' => 1,
                'theme'              => 'compact',
                'orientation'        => 'vertical',
                'wrap'               => 1,
            ],
        ]);
    }
}

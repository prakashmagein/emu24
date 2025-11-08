<?php

namespace Swissup\Navigationpro\Model\Menu\Builder;

class Megamenu extends \Swissup\Navigationpro\Model\Menu\Builder
{
    protected function prepareSettings()
    {
        return $this->setSettings([
            'max_depth' => 0,
            'identifier' => 'megamenu',
            'css_class' => 'navpro-effect-none',
            'dropdown_settings' => [
                'default' => [
                    'width' => 'small',
                    'layout' => [
                        [
                            'type' => 'children',
                            'columns_count' => 1,
                            'levels_per_dropdown' => 1,
                        ],
                    ],
                ],
                'level1' => [
                    'width' => 'fullwidth',
                    'position' => 'center',
                    'layout' => [
                        [
                            'type' => 'children',
                            'size' => 9,
                            'columns_count' => 4,
                            'direction' => 'horizontal',
                            'levels_per_dropdown' => 2,
                        ],
                        [
                            'type' => 'html',
                            'size' => 3,
                            'display_mode' => 'if_has_children',
                            'content' => <<<TEXT
<div style="overflow: hidden; max-height: 350px;">
    {{depend remote_entity.thumbnail}}
        <img class="xs-hide sm-hide md-show" src="{{media url=''}}/catalog/category/{{var remote_entity.thumbnail}}" alt="{{var item.name}}" loading="lazy"/>
    {{/depend}}
</div>
TEXT
,
                        ],
                    ],
                ],
            ],
        ]);
    }

    protected function prepareItems()
    {
        return $this->setItems([
            'home' => [
                'name' => 'Home',
                'url_path' => '',
                'css_class' => 'navpro-home',
            ],
            'categories' => [
                'method' => 'importCategories'
            ],
            'contacts' => [
                'name' => 'Contacts',
                'url_path' => 'contact',
                'css_class' => 'navpro-contacts',
            ],
        ]);
    }
}

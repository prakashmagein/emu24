<?php

namespace Swissup\Navigationpro\Model\Menu\Builder;

class MegamenuCategoryThumbnails extends Megamenu
{
    protected function prepareSettings()
    {
        parent::prepareSettings();

        return $this->updateSettings([
            'item_settings' => [
                'level2' => [
                    'html' => <<<HTML
<a href="{{var item.url}}" class="{{var item.class}}{{depend remote_entity.thumbnail}} navpro-a-with-thumbnail{{/depend}}">
    <span>{{var item.name}}</span>
    {{depend remote_entity.thumbnail}}
        <img class="xs-hide sm-hide md-show" src="{{media url=''}}/catalog/category/{{var remote_entity.thumbnail}}" alt="{{var item.name}}" loading="lazy" />
    {{/depend}}
</a>
HTML
                ],
            ],
        ]);
    }
}

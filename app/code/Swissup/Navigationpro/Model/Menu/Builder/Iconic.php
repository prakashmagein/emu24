<?php

namespace Swissup\Navigationpro\Model\Menu\Builder;

class Iconic extends Simple
{
    protected function prepareSettings()
    {
        parent::prepareSettings();

        return $this->updateSettings([
            'css_class' => 'navpro-effect-slidein justify-around caret-bottom navpro-iconic iconic-vertical',
            'item_settings' => [
                'level1' => [
                    'html' => <<<HTML
<a href="{{var item.url}}" class="{{var item.class}}">
    {{depend remote_entity.thumbnail}}
        <img width="50px" src="{{media url=''}}/catalog/category/{{var remote_entity.thumbnail}}" alt="{{var item.name}}"/>
    {{/depend}}
    <span>{{var item.name}}</span>
</a>
HTML
                ],
            ],
        ]);
    }
}

<?php

namespace Swissup\Navigationpro\Model\Menu\Builder;

class Slideout extends \Swissup\Navigationpro\Model\Menu\Builder
{
    protected function prepareSettings()
    {
        return $this->setSettings([
            'max_depth' => 0,
            'identifier' => 'slideout',
            'css_class' =>
                'navpro-slideout navpro-ribbon navpro-click ' .
                'navpro-vertical navpro-overlay navpro-effect-slideout',
        ]);
    }

    protected function prepareItems()
    {
        return $this->setItems([
            'header' => [
                'name' => 'Header',
                'url_path' => '',
                'css_class' => 'navpro-header xs-hide sm-hide md-show',
                'html' => 'Categories <span class="navpro-close" data-action="toggle-nav"></span>',
            ],
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

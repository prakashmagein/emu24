<?php

namespace Swissup\Navigationpro\Model\Menu\Builder;

class Ribbon extends Simple
{
    protected function prepareSettings()
    {
        parent::prepareSettings();

        return $this->setSettings([
            'max_depth' => 0,
            'identifier' => 'ribbon',
            'css_class' => 'navpro-ribbon navpro-effect-none',
        ]);
    }
}

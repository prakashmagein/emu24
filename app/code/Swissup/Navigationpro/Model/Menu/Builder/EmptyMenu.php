<?php

namespace Swissup\Navigationpro\Model\Menu\Builder;

class EmptyMenu extends \Swissup\Navigationpro\Model\Menu\Builder
{
    protected function prepareSettings()
    {
        return $this->setSettings([
            'max_depth' => 0,
            'identifier' => 'empty',
        ]);
    }
}

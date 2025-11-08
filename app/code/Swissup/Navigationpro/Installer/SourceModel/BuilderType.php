<?php

namespace Swissup\Navigationpro\Installer\SourceModel;

class BuilderType extends \Swissup\Navigationpro\Model\Config\Source\BuilderType
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $remove = [
            self::TYPE_EMPTY,
            self::TYPE_AMAZON_SIDEBAR,
            self::TYPE_SIDEBAR,
        ];

        foreach ($remove as $key) {
            unset($this->typeLabels[$key]);
        }

        return parent::toOptionArray();
    }
}

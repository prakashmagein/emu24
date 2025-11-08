<?php

namespace Swissup\Navigationpro\Model\Config\Source;

class BuilderType implements \Magento\Framework\Option\ArrayInterface
{
    const TYPE_EMPTY          = 'empty';
    const TYPE_AMAZON_TOP     = 'amazon_top';
    const TYPE_AMAZON_SIDEBAR = 'amazon_sidebar';
    const TYPE_ICONIC         = 'iconic';
    const TYPE_MEGAMENU       = 'megamenu';
    const TYPE_MEGAMENU_THUMBNAILS = 'megamenu_thumbnails';
    const TYPE_RIBBON         = 'ribbon';
    const TYPE_SIMPLE         = 'simple';
    const TYPE_SIDEBAR        = 'sidebar';
    const TYPE_SLIDEOUT       = 'slideout';
    const TYPE_STACKED        = 'stacked';

    /**
     * @var array
     */
    private $typeClasses = [
        self::TYPE_EMPTY            => '\Swissup\Navigationpro\Model\Menu\Builder\EmptyMenu',
        self::TYPE_AMAZON_TOP       => '\Swissup\Navigationpro\Model\Menu\Builder\AmazonTop',
        self::TYPE_AMAZON_SIDEBAR   => '\Swissup\Navigationpro\Model\Menu\Builder\AmazonSidebar',
        self::TYPE_ICONIC           => '\Swissup\Navigationpro\Model\Menu\Builder\Iconic',
        self::TYPE_MEGAMENU         => '\Swissup\Navigationpro\Model\Menu\Builder\Megamenu',
        self::TYPE_MEGAMENU_THUMBNAILS => '\Swissup\Navigationpro\Model\Menu\Builder\MegamenuCategoryThumbnails',
        self::TYPE_RIBBON           => '\Swissup\Navigationpro\Model\Menu\Builder\Ribbon',
        self::TYPE_SIMPLE           => '\Swissup\Navigationpro\Model\Menu\Builder\Simple',
        self::TYPE_SIDEBAR          => '\Swissup\Navigationpro\Model\Menu\Builder\Sidebar',
        self::TYPE_SLIDEOUT         => '\Swissup\Navigationpro\Model\Menu\Builder\Slideout',
        self::TYPE_STACKED          => '\Swissup\Navigationpro\Model\Menu\Builder\Stacked',
    ];

    /**
     * @var array
     */
    protected $typeLabels = [
        self::TYPE_EMPTY            => 'Empty Menu',
        self::TYPE_AMAZON_TOP       => 'Amazon Top Menu',
        self::TYPE_AMAZON_SIDEBAR   => 'Amazon Sidebar Menu',
        self::TYPE_ICONIC           => 'Iconic Menu',
        self::TYPE_MEGAMENU         => 'Megamenu',
        self::TYPE_MEGAMENU_THUMBNAILS => 'Megamenu with category thumbnails',
        self::TYPE_RIBBON           => 'Ribbon Menu',
        self::TYPE_SIMPLE           => 'Simple Menu',
        self::TYPE_SIDEBAR          => 'Sidebar Menu',
        self::TYPE_SLIDEOUT         => 'Slideout Menu',
        self::TYPE_STACKED          => 'Stacked Menu',
    ];

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->typeLabels as $key => $label) {
            $options[] = [
                'label' => $label,
                'value' => $key,
            ];
        }
        return $options;
    }

    /**
     * Get types
     *
     * @return array
     */
    public function toOptionHash()
    {
        return $this->getTypes();
    }

    /**
     * Get the list of supported type classes
     *
     * @return array
     */
    public function getTypeClasses()
    {
        return $this->typeClasses;
    }

    /**
     * Get the list of supported type labels
     *
     * @return array
     */
    public function getTypes()
    {
        return $this->typeLabels;
    }
}

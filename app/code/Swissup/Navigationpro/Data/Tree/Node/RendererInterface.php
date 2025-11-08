<?php
namespace Swissup\Navigationpro\Data\Tree\Node;

interface RendererInterface
{
    /**
     * Renders menu
     *
     * @param \Swissup\Navigationpro\Data\Tree\Node $item
     * @return string
     */
    public function render(\Swissup\Navigationpro\Data\Tree\Node $item);
}

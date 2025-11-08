<?php
namespace Swissup\Amp\Block;

class Js extends \Magento\Framework\View\Element\Template
{
    protected $items = [];

    public function addItem($type, $src, $async = true)
    {
        $this->items[$type] = [
            'src'   => $src,
            'async' => $async
        ];
    }

    public function getItems()
    {
        return $this->items;
    }
}

<?php
namespace Swissup\Reviewreminder\Block;

/**
 * Class Products List
 * @package Swissup\Reviewreminder\Block
 */
class Products extends \Magento\Framework\View\Element\Template
{
    public function getReviewLink($id)
    {
        return $this->getUrl('review/product/list', ['id'=> $id]);
    }
}

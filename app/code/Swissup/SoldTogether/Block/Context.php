<?php

namespace Swissup\SoldTogether\Block;

use Swissup\SoldTogether\Api\LinkedItemsProcessor;
use Swissup\SoldTogether\Model\Resolver\DataProvider\Products as DataProvider;
use Swissup\SoldTogether\Model\BlockState;

class Context
{
    /**
     * @var LinkedItemsProcessor
     */
    protected $itemsProcessor;

    /**
     * @var DataProvider
     */
    protected $dataProvider;

    /**
     * @var BlockState
     */
    protected $blockState;

    /**
     * @var \Magento\Catalog\Block\Product\Context
     */
    protected $productContext;

    /**
     * @param LinkedItemsProcessor                   $itemsProcessor
     * @param DataProvider                           $dataProvider
     * @param BlockState                             $blockState
     * @param \Magento\Catalog\Block\Product\Context $context
     */
    public function __construct(
        LinkedItemsProcessor $itemsProcessor,
        DataProvider $dataProvider,
        BlockState $blockState,
        \Magento\Catalog\Block\Product\Context $context
    ) {
        $this->itemsProcessor = $itemsProcessor;
        $this->dataProvider = $dataProvider;
        $this->blockState = $blockState;
        $this->productContext = $context;
    }

    public function getItemsProcessor()
    {
        return $this->itemsProcessor;
    }

    /**
     * @return DataProvider
     */
    public function getDataProvider()
    {
        return $this->dataProvider;
    }

    /**
     * @return BlockState
     */
    public function getBlockState()
    {
        return $this->blockState;
    }

    /**
     * @return \Magento\Catalog\Block\Product\Context
     */
    public function getProductContext()
    {
        return $this->productContext;
    }
}

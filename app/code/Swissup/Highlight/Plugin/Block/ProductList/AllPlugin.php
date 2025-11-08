<?php
declare(strict_types=1);

namespace Swissup\Highlight\Plugin\Block\ProductList;

use Swissup\Highlight\Block\ProductList\All as Block;
use Magento\Catalog\ViewModel\Product\OptionsData as ViewModel;
use Magento\Framework\ObjectManagerInterface;

class AllPlugin
{
    /**
     * @var ViewModel|null
     */
    private $viewModel = null;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        if (class_exists(ViewModel::class)) {
            $this->viewModel = $objectManager->create(ViewModel::class);
        }
    }

    /**
     * @param ExampleBlock $block
     * @return array
     */
    public function beforeToHtml(Block $block)
    {
        if ($this->viewModel) {
            $block->setData('viewModel', $this->viewModel);
            $block->assign('viewModel', $this->viewModel);
        }
        return [];
    }
}

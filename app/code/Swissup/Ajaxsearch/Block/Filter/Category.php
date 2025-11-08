<?php
/**
 * Copyright © 2018 Swissup. All rights reserved.
 */
namespace Swissup\Ajaxsearch\Block\Filter;

use Magento\Framework\View\Element\Template\Context;
use Magento\Catalog\Model\Layer\Filter\DataProvider\Category as CategoryDataProvider;

class Category extends \Swissup\Ajaxsearch\Block\Template
{
    /**
     * Catalog layer
     *
     * @var \Magento\Catalog\Model\Layer
     */
    protected $catalogLayer;

    /**
     * @var CategoryDataProvider
     */
    private $dataProvider;

    /**
     *
     * @var \Magento\Catalog\Model\Category
     */
    private $category;

    /**
     * Constructor
     *
     * @param Context $context
     * @param \Swissup\Ajaxsearch\Helper\Data $configHelper
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param \Magento\Catalog\Model\Layer\Filter\DataProvider\CategoryFactory $categoryDataProviderFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Swissup\Ajaxsearch\Helper\Data $configHelper,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Catalog\Model\Layer\Filter\DataProvider\CategoryFactory $categoryDataProviderFactory,
        array $data = []
    ) {
        parent::__construct($context, $configHelper, $data);

        $this->catalogLayer = $layerResolver->get();
        $this->dataProvider = $categoryDataProviderFactory->create(
            ['layer' => $this->catalogLayer]
        );
    }

    /**
     *
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection
     */
    public function getCategoryCollection()
    {
        $category = $this->getCategory();
        // $categories = $category->getChildrenCategories();
        $depth = $this->configHelper->getCategoryDepth();
        $categories = $category->getCategories($category->getId(), $depth, 'path', true);

        return $categories;
    }

    /**
     *
     * @return \Magento\Catalog\Model\Category
     */
    public function getCategory()
    {
        if ($this->category === null) {
            $this->category = $this->dataProvider->getCategory();
        }

        return $this->category;
    }

    /**
     *
     * @param \Magento\Catalog\Model\Category $category
     * @param  int $level
     * @param  string|boolean $char
     * @return string
     */
    public function getOptionName($category, $level = 0, $char = false)
    {
        $filler = '&nbsp;';

        $name = $this->escapeHtml($category->getName());

        $level = $category->getLevel() - $level + 1;

        if ($char !== false) {
            $filler = str_repeat($filler . $filler, $level - 1) . $char . $filler;
        } else {
            $filler = str_repeat($filler . $filler, $level) /* . '&lt;' . $filler*/;
        }

        return $filler . $name;
    }
}

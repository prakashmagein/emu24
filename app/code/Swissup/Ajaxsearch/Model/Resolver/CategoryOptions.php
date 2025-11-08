<?php
declare(strict_types=1);

namespace Swissup\Ajaxsearch\Model\Resolver;

use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Catalog\Model\Layer\Resolver;

class CategoryOptions implements ResolverInterface
{
    /**
     *
     * @var \Swissup\Ajaxsearch\Helper\Data
     */
    private $configHelper;

    /**
     * Catalog layer
     *
     * @var \Magento\Catalog\Model\Layer
     */
    private $catalogLayer;

    /**
     * @var \Magento\Catalog\Model\Layer\Filter\DataProvider\Category
     */
    private $categoryDataProvider;

    /**
     * Constructor
     *
     * @param \Swissup\Ajaxsearch\Helper\Data $configHelper
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param \Magento\Catalog\Model\Layer\Filter\DataProvider\CategoryFactory $categoryDataProviderFactory
     */
    public function __construct(
        \Swissup\Ajaxsearch\Helper\Data $configHelper,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Catalog\Model\Layer\Filter\DataProvider\CategoryFactory $categoryDataProviderFactory
    ) {
        $this->configHelper = $configHelper;
        $this->catalogLayer = $layerResolver->get();
        $this->categoryDataProvider = $categoryDataProviderFactory->create(
            ['layer' => $this->catalogLayer]
        );
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ) {
//        if (!isset($args['search']) /* && !isset($args['filter'])*/) {
//            throw new GraphQlInputException(
//                __("'search' or 'filter' input argument is required.")
//            );
//        }

        if (!isset($args['category'])) {
            $store = $context->getExtensionAttributes()->getStore();
            $args['category'] = $store->getRootCategoryId();
        }

        if ($args['category']) {
            $this->catalogLayer->setCurrentCategory($args['category']);
        }

        $baseSize = 1;
        $items = [
            [
                'id' => 0,
                'text' => __('All')
            ]
        ];

        $category = $this->categoryDataProvider->getCategory();
        $level = $category->getLevel();

        if ($category->isInRootCategoryList()) {
            $parentCategory = $category->getParentCategory();
            if ($parentCategory->isInRootCategoryList()) {
                $level = $parentCategory->getLevel();
                $items[] = [
                    'id' => $parentCategory->getId(),
                    'text' => $this->getOptionName($parentCategory, $level/*, '|'*/)
                ];
                $baseSize++;
            }
            $items[] = [
                'id' => $category->getId(),
                'text' => $this->getOptionName($category, $level/*, '|'*/)
            ];
            $baseSize++;
        }

        // $categories = $category->getChildrenCategories();
        $depth = $this->configHelper->getCategoryDepth();
        $collection = $category->getCategories(
            $category->getId(),
            $depth,
            'path',
            true
        );

        if (!empty($args['search'])) {
            $search = $args['search'];
            $search = '%' . $search . '%';
            $collection->addFieldToFilter('name', ['like' => $search]);
        }

        $allIds = $collection->getAllIds();
        foreach ($collection as $_category) {
            if (in_array($_category->getId(), $allIds)) {
                $items[] = [
                    'id' => $_category->getId(),
                    'text' => $this->getOptionName($_category, $level)
                ];
            }
        }

        return [
            'total_count' => $baseSize + $collection->getSize(),
            'items' => $items
        ];
    }

    /**
     *
     * @param  \Magento\Catalog\Model\Category $category
     * @param  int|string $level
     * @param  string|bool $char
     * @return string
     */
    public function getOptionName($category, $level = 0, $char = false)
    {
        $tamper = '&nbsp;';

//        $name = $this->escapeHtml($category->getName());
        $name = $category->getName();
        $level = $category->getLevel() - $level + 1;

        if ($char !== false) {
            $tamper = str_repeat($tamper . $tamper, $level - 1) . $char . $tamper;
        } else {
            $tamper = str_repeat($tamper . $tamper, $level) /* . '&lt;' . $tamper*/;
        }

        return $tamper . $name;
    }
}

<?php
declare(strict_types=1);

namespace Swissup\Highlight\Model\Resolver\DataProvider;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\InputException;

class Conditions extends \Magento\Framework\DataObject
{
    /**
     * @var \Swissup\Highlight\Helper\Conditions
     */
    private $highlightConditions;

    /**
     * @var \Magento\Rule\Model\Condition\Sql\Builder
     */
    private $sqlBuilder;

    /**
     * @var \Magento\CatalogWidget\Model\Rule
     */
    private $rule;

    /**
     * @var \Magento\Widget\Helper\Conditions
     */
    private $conditionsHelper;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param \Swissup\Highlight\Helper\Conditions $highlightConditions
     * @param \Magento\Rule\Model\Condition\Sql\Builder $sqlBuilder
     * @param \Magento\CatalogWidget\Model\RuleFactory $ruleFactory
     * @param \Magento\Widget\Helper\Conditions $conditionsHelper
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        \Swissup\Highlight\Helper\Conditions $highlightConditions,
        \Magento\Rule\Model\Condition\Sql\Builder $sqlBuilder,
        \Magento\CatalogWidget\Model\RuleFactory $ruleFactory,
        \Magento\Widget\Helper\Conditions $conditionsHelper,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        parent::__construct($data);

        $this->highlightConditions = $highlightConditions;
        $this->sqlBuilder = $sqlBuilder;
        $this->rule = $ruleFactory->create();
        $this->conditionsHelper = $conditionsHelper;
        $this->categoryRepository = $categoryRepository;
        $this->storeManager = $storeManager;
    }

    /**
     * Use this method to apply manual filters, etc
     *
     * @param  \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function attachToCollection($collection)
    {
        if ($this->highlightConditions->isModuleOutputEnabled('Smile_ElasticsuiteCatalog')) {
            return;
        }
        $conditions = $this->getConditions();
        $conditions->collectValidatedAttributes($collection);
        $this->sqlBuilder->attachConditionToCollection($collection, $conditions);
    }

    /**
     * Returns currently viewed, comma separated category ids, if
     * 'current' condition is used. Otherwise returns empty string.
     *
     * @return string
     */
    public function getCategoryIds()
    {
        $ids = (string) $this->getData('category_ids');
        if ($ids) {
            return $ids;
        }

        $categoryIds = [];
        foreach ($this->getConditionsDecoded() as $condition) {
            if (empty($condition['attribute'])
                || $condition['attribute'] !== 'category_ids'
                || !strstr($condition['value'], 'current')
            ) {
                continue;
            }

            $withChildren = (bool) strstr($condition['value'], 'current+');
            $categoryIds = $this->highlightConditions->getCurrentCategoryIds(
                $withChildren
            );

            if ($withChildren) {
                break;
            }
        }

        $ids = implode(',', $categoryIds);
        $this->setData('category_ids', $ids);

        return $ids;
    }

    /**
     * @param string $conditions
     * @return $this
     */
    public function setConditions($conditions)
    {
        $this->setData('conditions_encoded', $conditions);
        return $this;
    }

    /**
     * @return \Magento\Rule\Model\Condition\Combine
     */
    private function getConditions()
    {
        $conditions = $this->getConditionsDecoded();

        foreach ($conditions as $key => $condition) {
            if (!empty($condition['attribute'])) {
                if (in_array($condition['attribute'], ['special_from_date', 'special_to_date'])) {
                    $conditions[$key]['value'] = date('Y-m-d H:i:s', strtotime($condition['value']));
                }

                if ($condition['attribute'] === 'category_ids') {
                    $conditions[$key] = $this->updateChildrenCategoryConditions($condition);
                }
            }

            $conditions[$key] = $this->prepareCondition($conditions[$key]);
        }

        $this->rule->loadPost(['conditions' => $conditions]);

        return $this->rule->getConditions();
    }

    /**
     * @return array
     */
    public function getConditionsDecoded()
    {
        $conditions = $this->getData('conditions_encoded')
            ? $this->getData('conditions_encoded')
            : $this->getData('conditions');

        if ($conditions) {
            $conditions = $this->conditionsHelper->decode($conditions);
        }

        if (!$conditions) {
            $conditions = [];
        }

        return $conditions;
    }

    /**
     * Modify condition item if needed
     *
     * @param  array $condition
     * @return array
     */
    private function prepareCondition($condition)
    {
        if (empty($condition['attribute'])) {
            return $condition;
        }

        // 'current' category filter
        if ($condition['attribute'] === 'category_ids'
            && strstr($condition['value'], 'current')
        ) {
            $condition['value'] = str_replace(
                ['current', 'current+'],
                $this->getCategoryIds(),
                $condition['value']
            );

            $operator = $condition['operator'];
            switch ($condition['operator']) {
                case '==':
                    $operator = '()';
                    break;
                case '!=':
                    $operator = '!()';
                    break;
            }
            $condition['operator'] = $operator;
        }

        return $condition;
    }

    /**
     * Update conditions if the category is an anchor category
     *
     * @param array $condition
     * @return array
     */
    private function updateChildrenCategoryConditions($condition)
    {
        if (!isset($condition['value']) || !is_string($condition['value'])) {
            return $condition;
        }

        $changeOperator = false;
        $categoryIds = explode(',', $condition['value']);

        foreach ($categoryIds as $idWithPlus) {
            if (strpos($idWithPlus, '+') === false) {
                continue;
            }

            $id = (int) $idWithPlus;

            try {
                $category = $this->categoryRepository->get(
                    $id,
                    $this->storeManager->getStore()->getId()
                );
            } catch (\Exception $e) {
                continue;
            }

            $changeOperator = true;
            $children = $category->getChildren(true);
            $children = explode(',', $children);
            $condition['value'] = str_replace(
                $idWithPlus,
                implode(',', array_merge([$id], $children)),
                $condition['value']
            );
        }

        if ($changeOperator) {
            $operator = $condition['operator'];
            switch ($condition['operator']) {
                case '==':
                    $operator = '()';
                    break;
                case '!=':
                    $operator = '!()';
                    break;
            }
            $condition['operator'] = $operator;
        }

        return $condition;
    }
}

<?php

namespace Swissup\ChatGptAssistant\Model\Filter;

class Category extends Filter
{
    protected \Magento\Store\Model\StoreManagerInterface $storeManager;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Filter\FilterManager $filterManager
    ) {
        $this->storeManager = $storeManager;
        parent::__construct($filterManager);
    }

    /**
     * Sub-categories directive - return child categories
     *
     * @param  array $construction
     * @return string
     */
    public function subcatsDirective($construction)
    {
        $result = [];
        $params = $this->getIncludeParameters($construction[2]);
        foreach ($this->getScope()->getChildrenCategories() as $subcat) {
            $result[] = $subcat->getName();
        }

        return $this->postprocessResult($result, $params);
    }
}

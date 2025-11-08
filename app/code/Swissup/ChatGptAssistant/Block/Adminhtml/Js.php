<?php

namespace Swissup\ChatGptAssistant\Block\Adminhtml;

use Magento\Backend\Block\Template;

class Js extends Template
{
    private \Swissup\ChatGptAssistant\Model\ResourceModel\InputField\Collection $fields;

    private \Magento\Framework\Serialize\Serializer\Json $jsonEncoder;

    private \Swissup\ChatGptAssistant\Model\Filter\Product $productFilter;

    private \Swissup\ChatGptAssistant\Model\Filter\Category $categoryFilter;

    private \Magento\Framework\Registry $coreRegistry;

    private string $entityType = '';

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Swissup\ChatGptAssistant\Model\ResourceModel\InputField\Collection $fields,
        \Magento\Framework\Serialize\Serializer\Json $jsonEncoder,
        \Swissup\ChatGptAssistant\Model\Filter\Product $productFilter,
        \Swissup\ChatGptAssistant\Model\Filter\Category $categoryFilter,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->fields = $fields;
        $this->jsonEncoder = $jsonEncoder;
        $this->productFilter = $productFilter;
        $this->categoryFilter = $categoryFilter;
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * @return string
     */
    public function getSettingsJson()
    {
        $result = [
            'url' => $this->getUrl('swissup_assistant'),
            'fields' => []
        ];

        if ($scope = $this->getCurrentEntity()) {
            foreach ($this->fields->getItems() as $field) {
                $this->filterPrompts($field, $scope);
                $result['fields'][] = $field->getJsConfig();
            }
        }

        return $this->jsonEncoder->serialize($result);
    }

    /**
     * Filter field prompts
     *
     * @param \Swissup\ChatGptAssistant\Model\InputField $field
     * @param \Magento\Framework\Model\AbstractModel $scope
     * @return void
     */
    private function filterPrompts($field, $scope)
    {
        $jsConfig = $field->getJsConfig();
        $filter = $this->getCurrentFilter();
        $prompts = [];
        foreach ($field->getPrompts() as $id => $prompt) {
            $prompts[$id] = $prompt;
            $prompts[$id]['text'] = $filter
                ->setScope($scope)
                ->filter($prompt['text']);
        }
        $jsConfig['prompts'] = $prompts;
        $field->setJsConfig($jsConfig);
    }

    /**
     * Get current filter
     *
     * @return mixed
     */
    private function getCurrentFilter()
    {
        if ($this->entityType == \Magento\Catalog\Model\Product::ENTITY) {
            return $this->productFilter;
        }

        if ($this->entityType == \Magento\Catalog\Model\Category::ENTITY) {
            return $this->categoryFilter;
        }

        return null;
    }

    /**
     * Get current entity for filter
     *
     * @return mixed
     */
    private function getCurrentEntity()
    {
        if ($product = $this->coreRegistry->registry('product')) {
            $this->entityType = \Magento\Catalog\Model\Product::ENTITY;
            return $product;
        }

        if ($category = $this->coreRegistry->registry('category')) {
            $this->entityType = \Magento\Catalog\Model\Category::ENTITY;
            return $category;
        }

        return null;
    }
}

<?php

namespace Swissup\ChatGptAssistant\Model\Filter;

class Filter
{
    /**
     * Construction regular expression
     */
    protected $constructionPattern = '/{{([a-z,A-Z]{0,30})(.*?)}}/si';

    protected \Magento\Framework\Model\AbstractModel $scope;

    protected \Magento\Framework\Filter\FilterManager $filterManager;

    public function __construct(
        \Magento\Framework\Filter\FilterManager $filterManager
    ) {
        $this->filterManager = $filterManager;
    }

    /**
     * Set scope for filter
     *
     * @param \Magento\Framework\Model\AbstractModel $scope
     * @return $this
     */
    public function setScope($scope)
    {
        $this->scope = $scope;
        return $this;
    }

    /**
     * Get scope
     *
     * @return \Magento\Framework\Model\AbstractModel
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * Filter the string as template
     *
     * @param string $value
     * @return string
     */
    public function filter($value)
    {
        if (preg_match_all($this->constructionPattern, $value, $constructions, PREG_SET_ORDER)) {
            foreach ($constructions as $construction) {
                $replacedValue = '';
                $callback = [$this, $construction[1].'Directive'];
                if (is_callable($callback)) {
                    try {
                        $replacedValue = call_user_func($callback, $construction);
                    } catch (\Exception $e) {
                        throw $e;
                    }
                }

                $value = str_replace($construction[0], $replacedValue, $value);
            }
        }

        return $value;
    }

    /**
     * Language code directive
     *
     * @param  array $construction
     * @return string
     */
    public function langCodeDirective($construction)
    {
        return $this->getScope()->getStore()->getConfig(
            \Magento\Config\Model\Config\Backend\Admin\Custom::XML_PATH_GENERAL_LOCALE_CODE
        );
    }

    /**
     * Attribute directive
     *
     * @param  array $construction
     * @return string
     */
    public function attributeDirective($construction)
    {
        $params = $this->getIncludeParameters($construction[2]);
        $attributeCode = $params['code'] ?? '';
        $exclude = $params['exclude'] ?? '';
        $html = $params['html'] ?? '';
        $store = $params['store'] ?? null;
        $attributeValue = '';
        if ($attributeCode === '*') {
            $attributeValue = $this->getAllAttributes($exclude);
        } else {
            // attribute code can contain multiple attributes separated by comma
            $attributeCodes = array_map('trim', explode(",", $attributeCode));
            // add labels when multiple attributes
            $addLabel = count($attributeCodes) > 1;
            foreach ($attributeCodes as $code) {
                $attributeValue .= $this->getAttributeValue($code, $addLabel, $html, $store);
            }
        }

        return $this->postprocessResult($attributeValue, $params);
    }

    /**
     * Retrieve directive parameters
     *
     * @param mixed $value
     * @return array
     */
    protected function getIncludeParameters($value)
    {
        $tokenizer = new \Magento\Framework\Filter\Template\Tokenizer\Parameter();
        $tokenizer->setString($value);

        return $tokenizer->tokenize();
    }

    /**
     * Get all attributes
     *
     * @param string $exclude
     * @return string
     */
    protected function getAllAttributes($exclude)
    {
        $scope = $this->getScope();
        $typeInstance = $scope->getTypeInstance();
        $attributes = $scope->getAttributes();
        $excludedAttributeCodes = array_map('trim', explode(',', $exclude));
        $attributesStr = '';

        if ($typeInstance && method_exists($typeInstance, 'getConfigurableAttributes')) {
            foreach ($typeInstance->getConfigurableAttributes($scope) as $attribute) {
                $attributeCode = $attribute->getProductAttribute()->getAttributeCode();
                if (!in_array($attributeCode, $excludedAttributeCodes)) {
                    $attributesStr .= $this->getAttributeValue($attributeCode, true);
                }
            }
        }

        foreach ($attributes as $attribute) {
            if ($attribute->getIsVisibleOnFront()) {
                $attributeCode = $attribute->getAttributeCode();
                if (!in_array($attributeCode, $excludedAttributeCodes)) {
                    $attributesStr .= $this->getAttributeValue($attribute->getAttributeCode(), true);
                }
            }
        }

        return trim($attributesStr);
    }

    /**
     * Get attribute frontend value from $this->scope
     *
     * @param string $attributeCode
     * @param bool $addLabel
     * @param bool $html
     * @param int|null $store
     * @return string
     */
    protected function getAttributeValue($attributeCode, $addLabel, $html = false, $store = null)
    {
        $scope = $this->getScope();
        $typeInstance = $scope->getTypeInstance();
        $result = '';
        if ($typeInstance && method_exists($typeInstance, 'getConfigurableAttributes')) {
            // search for attribute values in simple products of configurable product
            foreach ($typeInstance->getConfigurableAttributes($scope) as $attribute) {
                if ($attributeCode == $attribute->getProductAttribute()->getAttributeCode()) {
                    $values = $attribute->getOptions() ?: [];
                    $label = $attribute->getLabel();
                    $result .= $addLabel ? $label . ': ' : '';
                    foreach ($values as $value) {
                        $result .= $value['store_label'] . ', ';
                    }
                    $result = trim($result, ', ') . '. ';
                }
            }
        }

        if ($attribute = $scope->getResource()->getAttribute($attributeCode)) {
            $value = '';

            if ($attribute->getFrontendInput() == 'gallery') {
                return $result;
            }

            if ($store) {
                $value = $scope->getResource()->getAttributeRawValue($scope->getId(), $attributeCode, $store);
            } elseif ($scope->getData($attributeCode)) {
                $attribute->setStoreId($scope->getStoreId());
                $value = $attribute->getFrontend()->getValue($scope);
            }

            if (!$value) {
                return $result;
            }

            $label = $attribute->getFrontend()->getLabel();
            if ($attribute->getBackendType() == 'decimal') {
                $value = (float)$value;
            } elseif (!$html && $attribute->getIsHtmlAllowedOnFront()) {
                $value = $this->filterManager->removeTags($value);
                // make string into one line
                $value = str_replace(["\r\n", "\r","\n"], ' ', $value);
            }

            $result .= $addLabel ? $label . ': ' . $value . '. ' : $value;
        }

        return $result;
    }

    /**
     * Process directive result before output
     *
     * @param array|string $result
     * @param array $params
     * @return string
     */
    protected function postprocessResult($result, $paramsArray)
    {
        $output = '';
        $params = new \Magento\Framework\DataObject($paramsArray);
        if ($result) {
            if (is_array($result)) {
                if ($params->hasExclude()) {
                    // exclude values
                    $exclude = explode(",", $params->getExclude());
                    $exclude = array_map('trim', $exclude);
                    $result = array_diff($result, $exclude);
                }

                if ($limit = (int)$params->getLimit()) {
                    $result = array_slice($result, 0, $limit, true);
                }

                $separator = $params->hasSeparator() ? $params->getSeparator() : ', ';
                $output = implode($separator, $result);
            } else {
                $output = $result;
            }

            if ($maxLength = (int)$params->getMaxLength()) {
                // cut the rest of string when max_length is set
                $output = $this->filterManager->truncate(
                    $output,
                    [
                        'length' => $maxLength,
                        'breakWords' => (bool)$params->getBreakWords(),
                        'etc' => $params->getEtc()
                    ]
                );
            }

            $output = $params->getPrefix() . $output . $params->getSufix();
        }

        return $output;
    }
}

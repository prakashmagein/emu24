<?php

namespace Swissup\Easybanner\Model\Rule\Condition;

use Magento\Framework\App\ObjectManager;

/**
 * Class Banner
 */
class Banner extends \Magento\Rule\Model\Condition\Product\AbstractProduct
{
    public function loadAttributeOptions()
    {
        $attributes = [
            'General Conditions' => [
                'category_ids'      => __('Category'),
                'product_ids'       => __('Product'),
                'handle'            => __('Page'),
                'url'               => __('Page URL'),
                'customer_group'    => __('Customer Group'),
            ],
            'Banner Statistics' => [
                'clicks_count'      => __('Clicks Count'),
                'display_count'     => __('Display Count (Temporarily Disabled)'),
            ],
            'Cart Conditions' => [
                'subtotal_excl'     => __('Subtotal (Excl.Tax)'),
                'subtotal_incl'     => __('Subtotal (Incl.Tax)'),
            ],
            'Date Conditions' => [
                'weekday'           => __('Day of Week'),
                'monthday'          => __('Day of Month'),
                'date'              => __('Current Date'),
                'time'              => __('Current Time'),
            ],
            'Lightbox and Awesomebar Conditions' => [
                'display_count_per_customer'            => __('Display Count per Customer'),
                'display_count_per_customer_per_day'    => __('Display Count per Customer (Per Day)'),
                'display_count_per_customer_per_week'   => __('Display Count per Customer (Per Week)'),
                'display_count_per_customer_per_month'  => __('Display Count per Customer (Per Month)'),
                'browsing_time'     => __('Customer browsing time (seconds)'),
                'inactivity_time'   => __('Customer inactivity time (seconds)'),
                'activity_time'     => __('Customer activity time (seconds)'),
                'scroll_offset'     => __('Scroll offset'),
                'exit_intent'       => __('Exit intent time (seconds)'),
            ],
        ];
        $this->setCombinedAttributes($attributes);

        $options = [];
        foreach ($attributes as $label => $values) {
            $options = array_merge($options, $values);
        }
        asort($options);
        $this->setAttributeOption($options);

        return $this;
    }

    public function getCombinedConditions()
    {
        $this->loadAttributeOptions();

        $result = [];
        foreach ($this->getCombinedAttributes() as $groupLabel => $values) {
            $attributes = [];
            foreach ($values as $code => $fieldLabel) {
                $attributes[] = [
                    'label' => $fieldLabel,
                    'value' => 'Swissup\Easybanner\Model\Rule\Condition\Banner|' . $code,
                ];
            }
            $result[] = [
                'label' => __($groupLabel),
                'value' => $attributes,
            ];
        }

        return $result;
    }

    public function getValue()
    {
        if (null === $this->getData('value')) {
            if ($this->getInputType() === 'time') {
                $this->setValue('00:00');
            } else if ($this->getInputType() === 'greater') {
                $this->setValue(0);
            }
        }

        return parent::getValue();
    }

    /**
     * Retrieve after element HTML
     *
     * @return string
     */
    public function getValueAfterElementHtml()
    {
        $html = '';

        switch ($this->getAttribute()) {
            case 'product_ids':
            case 'category_ids':
            case 'customer_group':
            case 'handle':
                $image = $this->_assetRepo->getUrl('images/rule_chooser_trigger.gif');
                break;
        }

        if (!empty($image)) {
            $html = '<a href="javascript:void(0)" class="rule-chooser-trigger"><img src="' .
                $image .
                '" alt="" class="v-middle rule-chooser-trigger" title="' .
                __(
                    'Open Chooser'
                ) . '" /></a>';
        }
        return $html;
    }

    /**
     * Retrieve value element chooser URL
     *
     * @return string
     */
    public function getValueElementChooserUrl()
    {
        $url = false;
        switch ($this->getAttribute()) {
            case 'product_ids':
                $url = '*/banner_widget/chooser/attribute/' . $this->getAttribute();
                if ($this->getJsFormObject()) {
                    $url .= '/form/' . $this->getJsFormObject();
                }
                break;

            case 'category_ids':
                $url = 'catalog_rule/promo_widget/chooser/attribute/' . $this->getAttribute();
                if ($this->getJsFormObject()) {
                    $url .= '/form/' . $this->getJsFormObject();
                }
                break;

            case 'customer_group':
                $url = '*/banner_widget/chooser/attribute/' . $this->getAttribute();
                if ($this->getJsFormObject()) {
                    $url .= '/form/' . $this->getJsFormObject();
                }
                break;

            case 'handle':
                $url = '*/banner_widget/chooser/attribute/' . $this->getAttribute();
                if ($this->getJsFormObject()) {
                    $url .= '/form/' . $this->getJsFormObject();
                }
                break;

            default:
                break;
        }

        return $url !== false ? $this->_backendData->getUrl($url) : '';
    }

    /**
     * Retrieve Explicit Apply
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getExplicitApply()
    {
        switch ($this->getAttribute()) {
            case 'product_ids':
            case 'category_ids':
            case 'customer_group':
            case 'handle':
            case 'date':
                return true;
            default:
                break;
        }
        if (is_object($this->getAttributeObject())) {
            switch ($this->getAttributeObject()->getFrontendInput()) {
                case 'date':
                    return true;
                default:
                    break;
            }
        }
        return false;
    }

    /**
     * Retrieve input type
     *
     * @return string
     */
    public function getInputType()
    {
        switch ($this->getAttribute()) {
            case 'category_ids':
            case 'product_ids':
            case 'customer_group':
            case 'handle':
            case 'weekday':
            case 'monthday':
                return 'grid';
            case 'date':
                return 'date';
            case 'time':
                return 'time';
            case 'inactivity_time':
            case 'activity_time':
            case 'browsing_time':
            case 'scroll_offset':
            case 'subtotal_excl':
            case 'subtotal_incl':
                return 'interval';
            case 'display_count':
            case 'clicks_count':
            case 'display_count_per_customer':
            case 'display_count_per_customer_per_day':
            case 'display_count_per_customer_per_week':
            case 'display_count_per_customer_per_month':
                return 'increment';
            case 'exit_intent':
                return 'greater';
            case 'url':
                return 'substring';
            default:
                return 'string';
        }
    }

    /**
     * Retrieve value element type
     *
     * @return string
     */
    public function getValueElementType()
    {
        if (in_array($this->getAttribute(), ['weekday', 'monthday'])) {
            return 'multiselect';
        } else if ($this->getAttribute() === 'date') {
            return 'date';
        }
        return parent::getValueElementType();
    }

    public function getValueSelectOptions()
    {
        if ($this->getAttribute() === 'weekday') {
            return ObjectManager::getInstance()
                ->get(\Magento\Config\Model\Config\Source\Locale\Weekdays::class)
                ->toOptionArray();
        }

        if ($this->getAttribute() === 'monthday') {
            $days = range(1, 31);
            foreach ($days as $key => $day) {
                $days[$key] = [
                    'value' => $day,
                    'label' => $day
                ];
            }
            return $days;
        }

        return parent::getValueSelectOptions();
    }

    /**
     * Add increment, time operators
     */
    public function loadOperatorOptions()
    {
        $this->setOperatorOption([
            '=='  => __('is'),
            '!='  => __('is not'),
            '>='  => __('equals or greater than'),
            '<='  => __('equals or less than'),
            '>'   => __('greater than'),
            '<'   => __('less than'),
            '{}'  => __('contains'),
            '!{}' => __('does not contain'),
            '()'  => __('is one of'),
            '!()' => __('is not one of'),
        ]);
        $this->setOperatorByInputType([
            'string' => ['==', '!=', '>=', '>', '<=', '<', '{}', '!{}', '()', '!()'],
            'substring' => ['{}', '!{}'],
            'numeric' => ['==', '!=', '>=', '>', '<=', '<', '()', '!()'],
            'increment' => ['<'],
            'greater' => ['>'],
            'interval' =>  ['<', '>'],
            'time' => ['>=', '<='],
            'date' => ['>=', '<='],
            'select' => ['==', '!='],
            'multiselect' => ['==', '!=', '{}', '!{}'],
            'grid' => ['()', '!()'],
        ]);

        return $this;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $model
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        $comparator = ObjectManager::getInstance()
            ->get(\Swissup\Easybanner\Helper\Condition::class)
            ->getValue($this->getAttribute(), $model);

        // client-side validation or disabled statistics filter
        if ($comparator === true) {
            return true;
        }

        return $this->validateAttribute($comparator);
    }

    /**
     * Do not use parent method as it buggy with multiselect components.
     * Also it does not correclty parse grid component value if it has
     * single value = 0 (Guest customer group for example)
     *
     * @return array|string|int|float
     */
    public function getValueParsed()
    {
        if (!$this->hasValueParsed()) {
            $value = $this->getData('value');

            if (in_array($this->getAttribute(), ['date', 'time'])) {
                $value = strtotime($value);
                $this->setValueParsed($value);
            } else {
                if ($this->isArrayOperatorType() && !is_array($value)) {
                    $value = preg_split('#\s*[,;]\s*#', $value, -1, PREG_SPLIT_NO_EMPTY);
                }
                $this->setValueParsed($value);
            }
        }

        return $this->getData('value_parsed');
    }
}

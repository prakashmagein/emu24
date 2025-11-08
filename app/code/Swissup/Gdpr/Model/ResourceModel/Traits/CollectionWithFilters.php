<?php

namespace Swissup\Gdpr\Model\ResourceModel\Traits;

trait CollectionWithFilters
{
    /**
     * @param string $field
     * @param array $condition
     */
    public function addFieldToFilter($field, $condition)
    {
        if (is_array($condition)) {
            $condition = current($condition);
        }
        return $this->addFilter($field, $condition);
    }

    /**
     * @return $this
     */
    protected function _renderFilters()
    {
        if ($this->_isFiltersRendered) {
            return $this;
        }

        $this->_items = array_filter($this->_items, function ($item) {
            foreach ($this->_filters as $filter) {
                $field = $filter->getField();
                $value = $filter->getValue();
                $method = 'filterBy' . ucfirst($field);

                if (method_exists($this, $method)) {
                    $result = $this->{$method}($value, $item);
                } else {
                    $result = $this->filterByField($field, $value, $item);
                }

                if ($result === false) {
                    return false;
                }
            }

            return true;
        });

        $this->_totalRecords = count($this->_items);
        $this->_isFiltersRendered = true;

        return $this;
    }

    /**
     * @param string $field
     * @param string $values
     * @param array $item
     * @return boolean
     */
    protected function filterByField($field, $values, $item)
    {
        if (!is_array($values)) {
            $values = [$values];
        }

        // process as OR statement
        foreach ($values as $value) {
            if (strpos($value, '!') === 0) {
                $value = substr($value, 1);
                if ($item[$field] != $value) {
                    return true;
                }

                continue;
            }

            if ($item[$field] == $value) {
                return true;
            }
        }

        return false;
    }
}

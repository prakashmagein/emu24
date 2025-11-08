<?php
namespace Swissup\Ajaxsearch\Model\Query\Catalog;

use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Search\Model\SearchCollectionFactory as CollectionFactory;
//use Magento\Tests\NamingConvention\true\string;
use Swissup\Ajaxsearch\Model\Query\CollectionFactory as QueryCollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb as DbCollection;
use Magento\Framework\Model\ResourceModel\AbstractResource;

use Swissup\Ajaxsearch\Helper\Data as ConfigHelper;
use Magento\Framework\Api\Search\SearchCriteria;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\View\Element\UiComponent\DataProvider\FilterPool;
use Magento\Framework\Api\FilterBuilder;

/**
 * Search query model
 */
class Category extends \Swissup\Ajaxsearch\Model\Query\WithFilterPool
{
    /**
     *
     * @var string
     */
    protected $name = 'catalog_category_listing_data_source';

    /**
     * @return string
     */
    protected function getName()
    {
        return $this->name;
    }

    /**
     * Retrieve suggest collection for query
     *
     * @return Collection
     */
    protected function _getSuggestCollection()
    {
        $collection = $this->_queryCollectionFactory
            ->setInstanceName(Collection::class)
            ->create()
            ->setStoreId($this->getStoreId())
            ->addIsActiveFilter()
            //->addRootLevelFilter()
            //->addLevelFilter(1)
            ->addNameToResult()
            ->joinUrlRewrite() //apply store filter
            ->addOrderField('path');

        $limit = $this->configHelper->getCategoryLimit();
        if ($limit) {
            $collection->setPageSize($limit);
        }
        return $this->applyFilters($collection);
    }

    /**
     *
     * @return array
     */
    protected function getFilters()
    {
        $filters = [];
        $value = $this->getQueryText();
        if ($value) {
            $_filters = ['name'];
            $values = ['%' . $value . '%'];
            // $values = [$value, '%' . $value, $value . '%', '%' . $value . '%'];
            foreach ($_filters as $filterAttribute) {
                foreach ($values as $_value) {
                    $filters[] = $this->filterBuilder
                        ->setConditionType('like')
                        ->setField($filterAttribute)
                        ->setValue($_value)
                        ->create();
                }
            }
            // $filter = $this->filterBuilder
            //     ->setConditionType('fulltext')
            //     ->setField('name')
            //     ->setValue($value)
            //     ->create();
            // $filters[] = $filter;
        }
        return $filters;
    }
}

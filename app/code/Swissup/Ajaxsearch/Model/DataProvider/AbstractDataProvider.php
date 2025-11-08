<?php
namespace Swissup\Ajaxsearch\Model\DataProvider;

use Magento\Search\Model\ResourceModel\Query\Collection;
use Swissup\Ajaxsearch\Model\QueryFactory;
use Swissup\Ajaxsearch\Model\Query;
use Magento\Search\Model\Autocomplete\DataProviderInterface;
use Magento\Search\Model\Autocomplete\ItemFactory;

use Swissup\Ajaxsearch\Helper\Data as ConfigHelper;

abstract class AbstractDataProvider extends \Magento\Framework\DataObject implements DataProviderInterface
{
    /**
     * Query factory
     *
     * @var QueryFactory
     */
    protected $queryFactory;

    /**
     * Autocomplete result item factory
     *
     * @var ItemFactory
     */
    protected $itemFactory;

    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * @param QueryFactory $queryFactory
     * @param ItemFactory $itemFactory
     * @param ConfigHelper $configHelper
     */
    public function __construct(
        QueryFactory $queryFactory,
        ItemFactory $itemFactory,
        ConfigHelper $configHelper
    ) {
        $this->queryFactory = $queryFactory;
        $this->itemFactory = $itemFactory;
        $this->configHelper = $configHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        return [];
    }

    /**
     * Get Query object
     *
     * @return \Magento\Search\Model\Query
     */
    protected function getQuery()
    {
        return $this->queryFactory->get();
    }

    /**
     * Retrieve suggest collection for query
     *
     * @return Collection
     */
    protected function getSuggestCollection()
    {
        return $this->getQuery()->getSuggestCollection();
    }
}

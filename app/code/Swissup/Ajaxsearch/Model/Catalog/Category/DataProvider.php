<?php
namespace Swissup\Ajaxsearch\Model\Catalog\Category;

use Magento\Search\Model\ResourceModel\Query\Collection;
use Swissup\Ajaxsearch\Model\Query\Catalog\Category as Query;
use Swissup\Ajaxsearch\Model\QueryFactory;
use Magento\Search\Model\Autocomplete\DataProviderInterface;
use Magento\Search\Model\Autocomplete\ItemFactory;
use Swissup\Ajaxsearch\Helper\Data as ConfigHelper;
use Magento\Catalog\Helper\Category as CategoryHelper;
use Swissup\Ajaxsearch\Model\DataProvider\AbstractDataProvider;

class DataProvider extends AbstractDataProvider implements DataProviderInterface
{
    /**
     *
     * @var CategoryHelper
     */
    protected $categoryHelper;

    /**
     * @param QueryFactory $queryFactory
     * @param ItemFactory $itemFactory
     * @param ConfigHelper $configHelper
     * @param CategoryHelper $categoryHelper
     */
    public function __construct(
        QueryFactory $queryFactory,
        ItemFactory $itemFactory,
        ConfigHelper $configHelper,
        CategoryHelper $categoryHelper
    ) {
        $this->categoryHelper = $categoryHelper;
        parent::__construct($queryFactory, $itemFactory, $configHelper);
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        $enable = $this->configHelper->isCategoryEnabled();
        if (!$enable) {
            return [];
        }
        $this->queryFactory->setInstanceName(Query::class);
        $collection = $this->getSuggestCollection();
        $query = $this->getQuery();
        $result = [];
        foreach ($collection as $item) {
            if ($this->categoryHelper->canShow($item) === false) {
                continue;
            }
            $resultItem = $this->itemFactory->create(
                array_merge($item->getData(), [
                    '_type' => 'category',
                    'title' => $item->getName(),
                    'num_results' => '',
                    // 'url' => $item->getUrl()
                    'url' => $this->categoryHelper->getCategoryUrl($item)
                ])
            );
            if ($resultItem->getTitle() == $query->getQueryText()) {
                array_unshift($result, $resultItem);
            } else {
                $result[] = $resultItem;
            }
        }
        // $result[] = $this->itemFactory->create([
        //     'title' => '',
        //     'num_results' => '',
        //     '_type' => 'debug',
        //     '_select' => (string) $collection->getSelect()
        // ]);
        return $result;
    }
}

<?php
namespace Swissup\Ajaxsearch\Model\Autocomplete;

use Swissup\Ajaxsearch\Model\Query\Autocomplete as Query;
use Swissup\Ajaxsearch\Model\Query\Autocomplete\Popular as PopularQuery;
use Magento\Search\Model\Autocomplete\DataProviderInterface;
use Swissup\Ajaxsearch\Model\DataProvider\AbstractDataProvider;

class DataProvider extends AbstractDataProvider implements DataProviderInterface
{
    const POPULAR_TERM = '__popular__';

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        $enable = $this->configHelper->isAutocompleteEnabled();
        if (!$enable) {
            return [];
        }

        $queryText = $this->getQuery()->getQueryText();

        $instanceName = Query::class;
        $isGetPopular = $queryText === self::POPULAR_TERM;
        if ($isGetPopular) {
            $instanceName = PopularQuery::class;
        }
        $this->queryFactory->setInstanceName($instanceName);
        $collection = $this->getSuggestCollection();
        $limit = $this->configHelper->getAutocompleteLimit();
        if ($limit) {
            $collection->setPageSize($limit);
        }

        $result = [];
        foreach ($collection as $item) {
            $resultItem = [
                'title' => $item->getQueryText(),
                'num_results' => $item->getNumResults(),
            ];
            if ($isGetPopular) {
                $resultItem['_type'] = 'popular';
            }
            $resultItem = $this->itemFactory->create($resultItem);
            if ($resultItem->getTitle() == $queryText) {
                array_unshift($result, $resultItem);
            } else {
                $result[] = $resultItem;
            }
        }
        return $result;
    }
}

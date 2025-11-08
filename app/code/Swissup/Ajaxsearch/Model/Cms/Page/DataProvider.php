<?php

namespace Swissup\Ajaxsearch\Model\Cms\Page;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Search\Model\ResourceModel\Query\Collection;
use Swissup\Ajaxsearch\Model\Query\Cms\Page as Query;
use Swissup\Ajaxsearch\Model\QueryFactory;
use Magento\Search\Model\Autocomplete\DataProviderInterface;
use Magento\Search\Model\Autocomplete\ItemFactory;
use Swissup\Ajaxsearch\Helper\Data as ConfigHelper;
use Swissup\Ajaxsearch\Model\DataProvider\AbstractDataProvider;

class DataProvider extends AbstractDataProvider implements DataProviderInterface
{

    /**
     * Cms page helper
     *
     * @var \Magento\Cms\Helper\Page
     */
    protected $cmsPageHelper;

    /**
     * @param QueryFactory $queryFactory
     * @param ItemFactory $itemFactory
     * @param ConfigHelper $configHelper
     * @param \Magento\Cms\Helper\Page $cmsPageHelper
     */
    public function __construct(
        QueryFactory $queryFactory,
        ItemFactory $itemFactory,
        ConfigHelper $configHelper,
        \Magento\Cms\Helper\Page $cmsPageHelper
    ) {
        parent::__construct($queryFactory, $itemFactory, $configHelper);
        $this->cmsPageHelper = $cmsPageHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        $enable = $this->configHelper->isPageEnabled();
        if (!$enable) {
            return [];
        }
        $this->queryFactory->setInstanceName(Query::class);
        $collection = $this->getSuggestCollection();
        $query = $this->getQuery();

        $result = [];
        foreach ($collection as $item) {
            $resultItem = $this->itemFactory->create([
                '_type' => 'page',
                'num_results' => '',
                'title' => $item->getTitle(),
                PageInterface::IDENTIFIER => $item->getIdentifier(),
                'url' => $this->cmsPageHelper->getPageUrl($item->getId()),
                'id' => $item->getId()
            ]);
            if ($resultItem->getTitle() == $query->getQueryText()) {
                array_unshift($result, $resultItem);
            } else {
                $result[] = $resultItem;
            }
        }
        return $result;
    }
}

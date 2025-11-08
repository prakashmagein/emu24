<?php
declare(strict_types=1);

namespace Swissup\Ajaxsearch\Model\Resolver;

use Swissup\Ajaxsearch\Model\Resolver\Items\Query\ItemQueryInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Catalog\Model\Layer\Resolver;

class Items implements ResolverInterface
{
    /**
     * @var ItemQueryInterface
     */
    private $searchQuery;

    /**
     * @param ItemQueryInterface $searchQuery
     */
    public function __construct(
        ItemQueryInterface $searchQuery
    ) {
        $this->searchQuery = $searchQuery;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ) {
        if ($args['currentPage'] < 1) {
            throw new GraphQlInputException(__('currentPage value must be greater than 0.'));
        }
        if ($args['pageSize'] < 1) {
            throw new GraphQlInputException(__('pageSize value must be greater than 0.'));
        }
        if (!isset($args['search']) /* && !isset($args['filter'])*/) {
            throw new GraphQlInputException(
                __("'search' or 'filter' input argument is required.")
            );
        }

        $searchResult = $this->searchQuery->getResult($args, $info, $context);

        if ($searchResult->getCurrentPage() > $searchResult->getTotalPages() && $searchResult->getTotalCount() > 0) {
            throw new GraphQlInputException(
                __(
                    'currentPage value %1 specified is greater than the %2 page(s) available.',
                    [$searchResult->getCurrentPage(), $searchResult->getTotalPages()]
                )
            );
        }

        $data = [
            'total_count' => $searchResult->getTotalCount(),
            'suggestions' => $searchResult->getSuggestionsSearchResult(),
            'page_info' => [
                'page_size' => $searchResult->getPageSize(),
                'current_page' => $searchResult->getCurrentPage(),
                'total_pages' => $searchResult->getTotalPages()
            ],
            // for aggregations
//            'search_result' => $searchResult,
//          'layer_type' => isset($args['search']) ? Resolver::CATALOG_LAYER_SEARCH : Resolver::CATALOG_LAYER_CATEGORY,
        ];

        return $data;
    }
}

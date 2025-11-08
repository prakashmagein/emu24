<?php

namespace Swissup\Ajaxsearch\Model\Resolver\Items\Query;

use Swissup\Ajaxsearch\Model\Resolver\Items\SearchResult;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;

/**
 * Search for products by criteria
 */
interface ItemQueryInterface
{
    /**
     * Get product search result
     *
     * @param array $args
     * @param ResolveInfo $info
     * @param ContextInterface $context
     * @return SearchResult
     */
    public function getResult(array $args, ResolveInfo $info, ContextInterface $context): SearchResult;
}

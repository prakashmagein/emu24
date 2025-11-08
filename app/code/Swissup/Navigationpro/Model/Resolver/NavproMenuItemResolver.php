<?php

declare(strict_types=1);

namespace Swissup\Navigationpro\Model\Resolver;

use Magento\Framework\GraphQl\Query\Resolver\TypeResolverInterface;

/**
 * {@inheritdoc}
 */
class NavproMenuItemResolver implements TypeResolverInterface
{
    /**
     * {@inheritdoc}
     */
    public function resolveType(array $data) : string
    {
        return 'NavproMenuItem';
    }
}

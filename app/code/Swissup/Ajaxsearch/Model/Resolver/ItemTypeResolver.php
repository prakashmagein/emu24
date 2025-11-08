<?php

declare(strict_types=1);

namespace Swissup\Ajaxsearch\Model\Resolver;

use Magento\Framework\GraphQl\Query\Resolver\TypeResolverInterface;

/**
 * {@inheritdoc}
 */
class ItemTypeResolver implements TypeResolverInterface
{
    /**
     * {@inheritdoc}
     */
    public function resolveType(array $data) : string
    {
        $type = 'autocomplete';
        if (isset($data['_type'])) {
            $type = $data['_type'];
        }
        $type = ucfirst($type);

        return 'AjaxsearchItem' . $type;
    }
}

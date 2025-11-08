<?php

declare(strict_types=1);

namespace Swissup\EasySlide\Model\Resolver;

use Magento\Framework\GraphQl\Query\Resolver\TypeResolverInterface;

/**
 * {@inheritdoc}
 */
class EasySliderConfigTypeResolver implements TypeResolverInterface
{
    /**
     * {@inheritdoc}
     */
    public function resolveType(array $data) : string
    {
        return 'EasySliderConfig';
    }
}

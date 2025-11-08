<?php

declare(strict_types=1);

namespace Swissup\Askit\Model\Resolver\Messages;

use Magento\Framework\GraphQl\Query\Resolver\IdentityInterface;

/**
 * Identity for resolved faqs
 */
class Identity implements IdentityInterface
{
    /**
     * @var string 
     */
    private $cacheTag = \Swissup\Askit\Model\Message::CACHE_TAG;

    /**
     * Get ids for cache tag
     *
     * @param array $resolvedData
     * @return string[]
     */
    public function getIdentities(array $resolvedData): array
    {
        $identities = [];
        $items = $resolvedData['questions'] ?? [];
        $idKey = 'id';
        foreach ($items as $item) {
            if (isset($item[$idKey])) {
                $identities[] = sprintf('%s_%s', $this->cacheTag, $item[$idKey]);
            }
        }

        if (!empty($identities)) {
            array_unshift($identities, $this->cacheTag);
        }

        return $identities;
    }
}

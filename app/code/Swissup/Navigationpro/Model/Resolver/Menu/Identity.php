<?php
declare(strict_types=1);

namespace Swissup\Navigationpro\Model\Resolver\Menu;

use Swissup\Navigationpro\Api\Data\MenuInterface;
use Magento\Framework\GraphQl\Query\Resolver\IdentityInterface;

/**
 * Identity for resolved CMS page
 */
class Identity implements IdentityInterface
{
    /** @var string */
    private $cacheTag = \Swissup\Navigationpro\Model\Menu::CACHE_TAG;

    /**
     * Get page ID from resolved data
     *
     * @param array $resolvedData
     * @return string[]
     */
    public function getIdentities(array $resolvedData): array
    {
        return empty($resolvedData[MenuInterface::MENU_ID]) ?
            [] : [$this->cacheTag, sprintf('%s_%s', $this->cacheTag, $resolvedData[MenuInterface::MENU_ID])];
    }
}

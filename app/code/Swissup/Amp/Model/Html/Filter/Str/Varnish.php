<?php
namespace Swissup\Amp\Model\Html\Filter\Str;

use Magento\PageCache\Model\Config as PageCacheConfig;

class Varnish
{
    /**
     * @var PageCacheConfig
     */
    protected $pageCacheConfig;

    /**
     * @param PageCacheConfig $pageCacheConfig
     */
    public function __construct(
        PageCacheConfig $pageCacheConfig
    ) {
        $this->pageCacheConfig = $pageCacheConfig;
    }

    /**
     * Varnish ESI fix: replace <include> tag with <esi:include>
     *
     * @param  string $html
     * @return string
     */
    public function process($html)
    {
        if ($this->pageCacheConfig->getType() != PageCacheConfig::VARNISH) {
            return $html;
        }

        $replaceMapping = [
            '<include src=' => '<esi:include src=',
            '</include>' => '</esi:include>'
        ];

        $result = $html;
        foreach ($replaceMapping as $search => $replace) {
            $result = str_replace($search, $replace, $result);
        }

        return $result;
    }
}

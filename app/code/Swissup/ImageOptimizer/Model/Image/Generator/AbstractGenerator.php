<?php
declare(strict_types=1);

namespace Swissup\ImageOptimizer\Model\Image\Generator;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AbstractGenerator implements \Swissup\ImageOptimizer\Api\ImageGeneratorInterface
{
    /**
     * Cache group Tag
     */
    const CACHE_GROUP = 'block_html';//\Magento\PageCache\Model\Cache\Type::TYPE_IDENTIFIER;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    private $cache;

    /**
     * @var \Magento\Framework\App\Cache\StateInterface
     */
    private $cacheState;

    /**
     *
     * @var string
     */
    protected $filenameFilter;

    /**
     *
     * @var integer
     */
    protected $pageSize = 100000;

    /**
     * @return \Generator
     * @throws \Exception
     */
    public function create(): \Generator
    {
        yield 0 => '';
    }

    /**
     *
     * @param \Magento\Framework\App\CacheInterface $cache
     * @param \Magento\Framework\App\Cache\StateInterface $cacheState
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\App\Cache\StateInterface $cacheState
    ) {
        $this->cache = $cache;
        $this->cacheState = $cacheState;
    }

    /**
     *
     * @param string $filename
     */
    public function setFilenameFilter($filename)
    {
        $this->filenameFilter = (string) $filename;
        return $this;
    }
    /**
     *
     * @param int $pageSize
     */
    public function setPageSize($pageSize)
    {
        $this->pageSize = (int) $pageSize;
        return $this;
    }

    /**
     *
     * @return string
     */
    protected function getCacheId()
    {
        $filename = (string) $this->filenameFilter;
        return hash('sha1', get_class($this) . $this->pageSize . '_' . $filename);
    }

    /**
     *
     * @return int|bool
     */
    protected function loadCurPage()
    {
        $isCacheable = $this->cacheState->isEnabled(self::CACHE_GROUP);
        if (!$isCacheable) {
            return false;
        }
        $cacheKey = $this->getCacheId();
        $cacheData = $this->cache->load($cacheKey);

        return $cacheData ? (int) $cacheData : false;
    }

    /**
     *
     * @return bool
     */
    protected function saveCurPage($page)
    {
        $isCacheable = $this->cacheState->isEnabled(self::CACHE_GROUP);
        if (!$isCacheable) {
            return false;
        }
        $cacheKey = $this->getCacheId();
        $cacheTags = [self::CACHE_GROUP];

        return $this->cache->save($page, $cacheKey, $cacheTags);
    }
}

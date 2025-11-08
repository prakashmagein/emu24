<?php
namespace Swissup\Pagespeed\Model\Optimizer;

use Swissup\Pagespeed\Helper\Config;

abstract class AbstractCachableOptimizer extends AbstractOptimizer
{
    /**
     * Cache group Tag
     */
    const CACHE_GROUP = \Magento\Framework\App\Cache\Type\Block::TYPE_IDENTIFIER;

    /**
     * Cache ID for file existence checks.
     */
    const CACHE_STATE_FILE_EXISTENCE = 'SW_PS_IMAGE_FILE_EXIST';

    /**
     *
     * @var \Magento\Framework\App\CacheInterface
     */
    private $cache;

    /**
     * Cache state
     *
     * @var \Magento\Framework\App\Cache\StateInterface
     */
    private $cacheState;

    /**
     *
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    /**
     *
     * @var array
     */
    private $cacheLayers = [];

    /**
     * @param Config $config
     * @param \Magento\Framework\App\CacheInterface $cache
     * @param \Magento\Framework\App\Cache\StateInterface $cacheState
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
     */
    public function __construct(
        Config $config,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\App\Cache\StateInterface $cacheState,
        \Magento\Framework\Serialize\Serializer\Json $serializer
    ) {
        $this->cache = $cache;
        $this->cacheState = $cacheState;
        $this->serializer = $serializer;

        parent::__construct($config);
    }


    /**
     * @param string $src
     * @return array|false
     */
    protected function loadCache($src)
    {
        $isCacheable = $this->cacheState->isEnabled(self::CACHE_GROUP);
        if (!$isCacheable) {
            return false;
        }
        $cacheKey = sha1($src);
        $cacheData = $this->cache->load($cacheKey);

        if ($cacheData) {
            try {
                $cacheData = $this->serializer->unserialize($cacheData);
            } catch (\InvalidArgumentException $e) {
                $cacheData = false;
            }
        }

        return $cacheData;
    }

    /**
     *
     * @param string $src
     * @param array $data
     * @return boolean
     */
    protected function saveCache($src, $data)
    {
        if (!$this->cacheState->isEnabled(self::CACHE_GROUP)) {
            return false;
        }
        $cacheKey = sha1($src);
        $cacheTags = [self::CACHE_GROUP];

        $data = $this->serializer->serialize($data);
        return $this->cache->save($data, $cacheKey, $cacheTags);
    }

    /**
     *
     * @param string $cacheLayerId
     */
    protected function _loadCacheLayer($cacheLayerId)
    {
        if (!isset($this->cacheLayers[$cacheLayerId])) {
            $cachedLayerData = $this->loadCache($cacheLayerId);
            $this->cacheLayers[$cacheLayerId] = $cachedLayerData ? $cachedLayerData : [];
        }

        if (!isset($this->cacheLayers[$cacheLayerId])) {
            $this->cacheLayers[$cacheLayerId] = [];
        }
    }

    /**
     *
     * @param  string $cacheLayerId
     * @param  string $id
     * @return mixed
     */
    protected function loadCacheLayerValue($cacheLayerId, $id)
    {
        $this->_loadCacheLayer($cacheLayerId);
        return isset($this->cacheLayers[$cacheLayerId][$id]) ? $this->cacheLayers[$cacheLayerId][$id] : false;
    }

    /**
     *
     * @param string $cacheLayerId
     * @param string $id
     * @param mixed $value
     */
    protected function setCacheLayerValue($cacheLayerId, $id, $value)
    {
        $this->_loadCacheLayer($cacheLayerId);
        $this->cacheLayers[$cacheLayerId][$id] = $value;

        return $this;
    }

    /**
     *
     * @param string $cacheLayerId
     * @return $this
     */
    protected function saveCacheLayer($cacheLayerId)
    {
        $value = isset($this->cacheLayers[$cacheLayerId]) ? $this->cacheLayers[$cacheLayerId] : [];
        if (!empty($value)) {
            $this->saveCache($cacheLayerId, $value);
        }
        return $this;
    }

    /**
     * Executes a callable and caches its result.
     *
     * @param string $cacheKeyPrefix A prefix for the cache key (e.g., 'media', 'pub_static').
     * @param string $dataId The unique ID for the data being cached (e.g., image URL).
     * @param callable $callback The callable function/method to execute if data is not in cache.
     * It should return the actual result (e.g., true/false for existence).
     * @param array $callbackArgs Arguments to pass to the callable.
     * @param bool $useCache Whether to use cache or force re-execution.
     * @return mixed The result from cache or from the callable.
     */
    protected function executeWithCache(
        string $cacheKeyPrefix,
        string $dataId,
        callable $callback,
        array $callbackArgs = [],
        bool $useCache = true
    ) {
        $fullCacheKey = $cacheKeyPrefix . '_' . hash('sha1', $dataId);
        $cachedResult = $useCache ? $this->loadCacheLayerValue(self::CACHE_STATE_FILE_EXISTENCE, $fullCacheKey) : false;

        if ($cachedResult === false) {
            // Execute the original "heavy" function
            $actualResult = call_user_func_array($callback, $callbackArgs);

            if ($useCache) {
                // Store 1 for true, 0 for false, to distinguish from 'false' (cache miss)
                $this->setCacheLayerValue(self::CACHE_STATE_FILE_EXISTENCE, $fullCacheKey, $actualResult ? 1 : 0);
            }
            return $actualResult;
        }

        // Return boolean based on cached value (1 or 0)
        return (bool) $cachedResult;
    }
}

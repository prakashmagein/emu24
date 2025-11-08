<?php
namespace Swissup\Pagespeed\Model\Image;

use Swissup\Image\Helper\Dimensions;

/**
 * @mixin \Swissup\Pagespeed\Model\Optimizer\AbstractCachableOptimizer
 */
trait DimensionsTrait
{
    /**
     * @var Dimensions
     */
    private $imageSize;

    /**
     * @param Dimensions $imageSize
     * @return void
     */
    public function setImageSizeHelper(Dimensions $imageSize): void
    {
        $this->imageSize = $imageSize;
    }

    /**
     * Get image dimensions from cache or filesystem
     *
     * @param string $src
     * @return array|false
     */
    protected function getDimensions(string $src)
    {
        $class = \Swissup\Pagespeed\Model\Optimizer\AbstractCachableOptimizer::class;
        if (!$this instanceof $class) {
            throw new \LogicException(
                static::class . " must extend $class to use DimensionsTrait"
            );
        }

        $dimensions = $this->loadCache($src);
        if ($dimensions === false) {
            $dimensions = $this->imageSize->getDimensions($src);
            $this->saveCache($src, $dimensions);
        }

        return $dimensions;
    }
}

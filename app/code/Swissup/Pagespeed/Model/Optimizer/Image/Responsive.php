<?php
namespace Swissup\Pagespeed\Model\Optimizer\Image;

use Magento\Framework\App\Response\Http as ResponseHttp;
use Swissup\Pagespeed\Model\Optimizer\Image\AbstractImage;
use Swissup\Pagespeed\Model\Image\DimensionsTrait;

class Responsive extends AbstractImage
{
    use DimensionsTrait;

    private \Swissup\Pagespeed\Model\Image\File $ioFile;

    /**
     * @param \Swissup\Pagespeed\Helper\Config $config
     * @param \Magento\Framework\App\CacheInterface $cache
     * @param \Magento\Framework\App\Cache\StateInterface $cacheState
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
     * @param \Swissup\Image\Helper\Dimensions $imageSize
     */
    public function __construct(
        \Swissup\Pagespeed\Helper\Config $config,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\App\Cache\StateInterface $cacheState,
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        \Swissup\Image\Helper\Dimensions $imageSize,
        \Swissup\Pagespeed\Model\Image\File $ioFile
    ) {
        parent::__construct($config, $cache, $cacheState, $serializer);
        $this->setImageSizeHelper($imageSize);
        $this->ioFile = $ioFile;
    }

    /**
     * Perform result postprocessing
     *
     * @param ResponseHttp $response
     * @return ResponseHttp
     */
    public function process(?ResponseHttp $response = null)
    {

        if (!$this->config->isImageResponsiveEnable() || $response === null) {
            return $response;
        }
        \Magento\Framework\Profiler::start(__METHOD__);
        $html = $response->getBody();

        $images = $this->getImagesFromHtml($html);
        // $allowedExtensions = explode(',', 'jpeg,jpg,png');

        // $resolutions = [
        //     // '160w', '240w', '320w', '640w', '1280w'
        //     '160', '240', '320', '640', '1280'
        // ];
        //
        $productResolutions = [0.5 ,0.75, 1, 2, 3];
        $cmsResolutions = [0.5 ,0.75, 1];
        $defaultSizes = $this->config->getDefaultImageResponsiveSizes();
        foreach ($images as $image) {
            $imageHTML = $image;
            /** @var \DOMElement $node */
            $node = $this->getDOMNodeFromImageHtml($imageHTML);

            $srcsetValue = $node->getAttribute('srcset');
            if (!empty($srcsetValue)) {
                continue;
            }

            $srcValue = $node->getAttribute('src');
            if (empty($srcValue)) {
                continue;
            }

            $ioFile = $this->ioFile;
            $isMediaCustomUrl = $ioFile->isMediaCustomUrl($srcValue);
            if (!$ioFile->isMediaProductUrl($srcValue) && !$isMediaCustomUrl) {
                continue;
            }
            $basename = $ioFile->getFileBasename($srcValue);
            // $filename = pathinfo($basename, PATHINFO_BASENAME);
            $dimensions = $this->getDimensions($srcValue);
            if (empty($dimensions['width'])) {
                continue;
            }

            $sizes = [];
            $srcset = [];
            $width = $dimensions['width'];
            $resolutions = $isMediaCustomUrl ? $cmsResolutions : $productResolutions;

            // https://developer.mozilla.org/en-US/docs/Learn/HTML/Multimedia_and_embedding/Responsive_images
            // The browser ignores everything after the first matching condition, so be careful how you order the media conditions.
            $sizes['max-width: 480px'] = $isMediaCustomUrl ? '(max-width: 480px) 100vw' : '(max-width: 480px) 50vw';

            foreach ($resolutions as $resolution) {
                if ($resolution !== 1) {
                    $urlPath = str_replace($basename, $resolution . 'x' . DIRECTORY_SEPARATOR . $basename, $srcValue);
                } else {
                    $urlPath = $srcValue;
                }
                if ($this->isMediaImageFileExist($urlPath, false) === false) {
                    continue;
                }
                $size = ceil($width * $resolution);

                if ($size > 2 * 40) {
                    // $srcset[$resolution . 'x'] = $urlPath . ' ' . $resolution . 'x';
                    $srcset[$resolution . 'w'] = $urlPath . ' ' . $size . 'w';
                    $sizes["{$resolution}"] = "(max-width: " . $size . "px) " . ($size - 40) . 'px';
                }
            }

            if (count($srcset) <= 1) {
                continue;
            }

            // $sizes[] = '(max-width: 768px) ' . ceil($width * 3 / 4) . 'px';
            $sizes["{$resolution}"] = $width . 'px';
            $sizes = empty($defaultSizes) || $isMediaCustomUrl ? implode(',', $sizes) : $defaultSizes;

            $node->setAttribute('srcset', implode(',', $srcset));
            $node->setAttribute('sizes', $sizes);

            $_image = $this->getImageHtmlFromDOMNode($node);

            if (empty($_image) || strpos($_image, '="\"') !== false) {
                continue;
            }
            $html = str_replace($image, $_image, $html);
        }

        $response->setBody($html);
        \Magento\Framework\Profiler::stop(__METHOD__);
        return $response;
    }

    /**
     * @param $imageUrl
     * @param $useCache
     * @return bool|mixed
     */
    protected function isMediaImageFileExist($imageUrl, $useCache = true)
    {
        return $this->executeWithCache(
            'media',
            $imageUrl,
            [$this->ioFile, 'isMediaImageFileExist'],
            [$imageUrl],
            $useCache
        );
    }
}

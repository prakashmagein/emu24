<?php
namespace Swissup\Pagespeed\Model\Optimizer\Image;

use Magento\Framework\App\Response\Http as ResponseHttp;
use Swissup\Pagespeed\Helper\Config;
use Swissup\Pagespeed\Model\Image\File;
use Swissup\Pagespeed\Model\Image\WebP\PictureTagBuilder;

class WebP extends AbstractImage
{
    /**
     * Cache state name
     */
    const CACHE_LAYER_WEBP_OPTIMIZER = 'SW_PS_WEBP_OPTIMIZER_DATA';

    /**
     * @var \Swissup\Pagespeed\Model\Image\File
     */
    private File $ioFile;

    private $imageAttributeParser;

    private $webpUrlResolver;

    private PictureTagBuilder $pictureTagBuilder;

    private $jsReplacer;

    /**
     * @param Config $config
     * @param \Magento\Framework\App\CacheInterface $cache
     * @param \Magento\Framework\App\Cache\StateInterface $cacheState
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
     * @param File $ioFile
     * @param \Swissup\Pagespeed\Model\Image\ImageAttributeParser $imageAttributeParser
     * @param \Swissup\Pagespeed\Model\Image\WebP\UrlResolver $webpUrlResolver
     * @param PictureTagBuilder $pictureTagBuilder
     * @param \Swissup\Pagespeed\Model\Image\WebP\JsReplacer $jsReplacer
     */
    public function __construct(
        \Swissup\Pagespeed\Helper\Config $config,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\App\Cache\StateInterface $cacheState,
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        \Swissup\Pagespeed\Model\Image\File $ioFile,
        \Swissup\Pagespeed\Model\Image\ImageAttributeParser $imageAttributeParser,
        \Swissup\Pagespeed\Model\Image\WebP\UrlResolver $webpUrlResolver,
        \Swissup\Pagespeed\Model\Image\WebP\PictureTagBuilder $pictureTagBuilder,
        \Swissup\Pagespeed\Model\Image\WebP\JsReplacer $jsReplacer
    ) {
        parent::__construct($config, $cache, $cacheState, $serializer);
        $this->ioFile = $ioFile;
        $this->imageAttributeParser = $imageAttributeParser;
        $this->webpUrlResolver = $webpUrlResolver;
        $this->pictureTagBuilder = $pictureTagBuilder;
        $this->jsReplacer = $jsReplacer;
    }

    /**
     * Perform result postprocessing
     *
     * @param ResponseHttp $response
     * @return ResponseHttp
     */
    public function process(?ResponseHttp $response = null)
    {
        if (!$this->config->isWebPSupport() || $response === null) {
            return $response;
        }
        \Magento\Framework\Profiler::start(__METHOD__);
        $html = $response->getBody();

        $images = $this->getImagesFromHtml($html);

        $isAddPictureTag = $this->config->isWebPAddPictureTag();
        $_imageHTML = '';
        $srcAttributes = $this->imageAttributeParser->getSupportedAttributeNames();
        $alreadyReplacedImages = [];

        foreach ($images as $imageHTML) {
            if (isset($alreadyReplacedImages[$imageHTML])) {
                continue;
            }
            $_imageHTML = $imageHTML;
            $_imageHTML = preg_replace('/\\\\/', '', $_imageHTML);
            $hasSlashes = $_imageHTML !== $imageHTML;

            /** @var \DOMElement $node */
            $node = $this->getDOMNodeFromImageHtml($_imageHTML);
            $originalNode = clone $node;

            $hasWebPImageUrl = false;
            $webPImageUrl = $imageUrl = false;
            foreach ($srcAttributes as $attrName) {
                $attrValue = $node->getAttribute($attrName);
                if (empty($attrValue)) {
                    continue;
                }
                $imageUrls = $this->imageAttributeParser->parseAttributeValue($attrName, $attrValue);
                $webPImageUrl = false;

                $replaces = [];
                foreach ($imageUrls as $imageUrl) {
                    $imageUrl = trim($imageUrl);
                    if (empty($imageUrl)) {
                        continue;
                    }

                    $webPImageUrl = $this->webpUrlResolver->resolve($imageUrl);
                    if (empty($webPImageUrl)) {
                        continue;
                    }

                    // $headers = @get_headers($webPImageUrl);
                    // if ($headers === false || strpos($headers[0], '404') !== false) {
                    //     continue;
                    // }
                    $hasWebPImageUrl = true;
                    $replaces[$imageUrl] = $webPImageUrl;
                }
                $attrValue = strtr($attrValue, $replaces);

                $node->setAttribute($attrName, $attrValue);
            }
            $newImageHTML = $this->getImageHtmlFromDOMNode($node);

            if ($isAddPictureTag &&
                $hasWebPImageUrl &&
                $imageHTML !== $newImageHTML
            ) {
                $isImgAlreadyInsidePicture = ($node->parentNode && $node->parentNode->nodeName === 'picture');

                $newPicutureHTML = $this->pictureTagBuilder->build($originalNode);

                // Original IMG into picture as fallback for really old browsers
                $isReplaceOriginImageHtml = true;
                $newImageHTML = str_replace(
                    '</picture>',
                    ($isReplaceOriginImageHtml ? $newImageHTML : $imageHTML) . '</picture>',
                    $newPicutureHTML
                );

                // When IMG is already in PICTURE remove wrapper PICTURE from generated HTML
                if ($isImgAlreadyInsidePicture) {
                    $newImageHTML = str_replace(['<picture>', '</picture>'], '', $newImageHTML);
                }
            }

            if ($hasSlashes) {
                $newImageHTML = addslashes($newImageHTML);
                $newImageHTML = str_replace('/', '\/', $newImageHTML);
            }
            if (empty($newImageHTML) || strpos($newImageHTML, '="\"') !== false) {
                continue;
            }
            $alreadyReplacedImages[$imageHTML] = true;
            $html = str_replace($imageHTML, $newImageHTML, $html);
        }

        if ($this->config->isReplaceWebPInJs()) {
            $html = $this->jsReplacer->replace($html);
        }

        $this->saveCacheLayer(self::CACHE_LAYER_WEBP_OPTIMIZER);
        $response->setBody($html);
        \Magento\Framework\Profiler::stop(__METHOD__);
        return $response;
    }

    /**
     *
     * @param  string  $imageUrl
     * @return boolean
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

    /**
     *
     * @param  string  $imageUrl
     * @return boolean
     */
    protected function isPubStaticImageFileExist($imageUrl, $useCache = true)
    {
        return $this->executeWithCache(
            'pub_static',
            $imageUrl,
            [$this->ioFile, 'isPubStaticImageFileExist'],
            [$imageUrl],
            $useCache
        );
    }
}

<?php
namespace Swissup\Pagespeed\Model\Optimizer\Image;

use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\App\Response\Http as ResponseHttp;
use Swissup\Pagespeed\Helper\Config;

class Lcp extends AbstractImage
{
    /**
     *
     * @var \Swissup\Pagespeed\Model\Preload
     */
    private $preloader;

    /**
     * @var array
     */
    private $preload = [];

    /**
     * @param Config $config
     * @param \Magento\Framework\App\CacheInterface $cache
     * @param \Magento\Framework\App\Cache\StateInterface $cacheState
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
     * @param \Swissup\Pagespeed\Model\Preload $preloader
     */
    public function __construct(
        Config $config,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\App\Cache\StateInterface $cacheState,
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        \Swissup\Pagespeed\Model\Preload $preloader
    ) {
        parent::__construct($config, $cache, $cacheState, $serializer);
        $this->preloader = $preloader;
    }

    private function isCmsPage($body): bool
    {
        return $body && strpos($body->getAttribute('class'), 'cms-page-view') !== false;
    }

    private function isHomePage($body): bool
    {
        return $body && strpos($body->getAttribute('class'), 'cms-index-index') !== false;
    }

    private function isProductPage($body): bool
    {
        return $body && strpos($body->getAttribute('class'), 'catalog-product-view') !== false;
    }

    private function isCategoryPage($body): bool
    {
        return $body && strpos($body->getAttribute('class'), 'catalog-category-view') !== false;
    }

    private function walkImgNodes($nodes, $maxLinksToAdd = 2)
    {
        foreach ($nodes as $i => $node) {
            if (!$node->getAttribute('src')) {
                continue;
            }

//            // remove 'loading="lazy"' from the first and second listing product images
//            if ($node->getAttribute('loading') === 'lazy') {
//                $class = (string) $node->getAttribute('class');
//                if (strpos($class, 'product-image-photo') !== false) {
//                    $node->removeAttribute('loading');
//                } else {
//                    continue;
//                }
//            }

            $attributes = [
                'as' => 'image',
                'href' => $node->getAttribute('src'),
                'imagesrcset' => $node->getAttribute('srcset'),
                'imagesizes' => $node->getAttribute('sizes'),
            ];

            $class = (string) $node->getAttribute('class');
            if (strpos($class, 'pagebuilder-mobile-hidden') !== false) {
                $attributes['media'] = '(min-width: 768px)';
            } elseif (strpos($class, 'pagebuilder-mobile-only') !== false) {
                $attributes['media'] = '(max-width: 767.98px)';
            }

            $this->addPreloadLink($attributes);

            if ($i + 1 >= $maxLinksToAdd) {
                break;
            }
        }
    }

    private function walkBackgroundImgNodes($nodes, $maxLinksToAdd = 1, $maxNodesToProcess = 4)
    {
        $linksAdded = 0;

        foreach ($nodes as $i => $node) {
            if ($i >= $maxNodesToProcess || $linksAdded >= $maxLinksToAdd) {
                break;
            }

            $attr = $node->getAttribute('data-background-images');
            $attr = json_decode(stripslashes($attr), true);
            if (!$attr || empty($attr['desktop_image'])) {
                continue;
            }

            $attributes = [
                'as' => 'image',
                'href' => $attr['desktop_image'],
            ];

            if (!empty($attr['mobile_image'])) {
                $attributes['imagesrcset'] = $attr['mobile_image'] . ' 768w, ' . $attr['desktop_image'];
                $attributes['imagesizes'] = '100vw';
            }

            $linksAdded++;
            $this->addPreloadLink($attributes);
        }

        return $linksAdded;
    }

    private function addPreloadLink($item)
    {
        $this->preload[] = $item;
        return $this;
    }

    /**
     * Perform result postprocessing
     *
     * @param ResponseHttp $response
     * @return ResponseHttp
     */
    public function process(?ResponseHttp $response = null)
    {
        if (!$this->config->isEnabled() || $response === null) {
            return $response;
        }
        $html = $response->getBody();

        $document = $this->getDomDocument($html);
        $xpath = new \DOMXPath($document);

        $body = $document->getElementsByTagName('body')->item(0);
        $content = $document->getElementById('maincontent');

        if ($this->isHomePage($body) || $this->isCmsPage($body)) {
            if (!$this->walkBackgroundImgNodes($xpath->query('//div[@data-background-images]', $content))) {
                $selector = '//div[@class="column main"]//img[not(ancestor::picture) and not(@loading="lazy")]';
                $this->walkImgNodes($xpath->query($selector), 1);
                $selector = '//div[@class="easyslide-wrapper"]//img[not(@loading="lazy")]';
                $this->walkImgNodes($xpath->query($selector));
                $selector = '//img[@class="product-image-photo"]';
                $this->walkImgNodes($xpath->query($selector, $content));
            }
        } elseif ($this->isProductPage($body)) {
            $this->walkImgNodes($xpath->query('(//img[@class="gallery-placeholder__image"])[1]', $content));
            $this->walkImgNodes($xpath->query('(//img[@class="fotorama__img"])[1]', $content));
            $this->walkImgNodes($xpath->query('(//img[@class="main-image"])[1]', $content));
        } else {
            $this->walkImgNodes($xpath->query('//div[@class="category-image"]//img', $content), 1);
            $this->walkImgNodes($xpath->query('//img[@class="product-image-photo"]', $content));
        }

        $this->preloader->add($this->preload, 'image', 'preload');

        return $response;
    }
}

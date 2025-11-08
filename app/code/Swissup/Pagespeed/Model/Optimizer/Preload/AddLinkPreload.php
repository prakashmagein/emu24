<?php
namespace Swissup\Pagespeed\Model\Optimizer\Preload;

use Swissup\Pagespeed\Helper\Config;
use Magento\Framework\App\Response\Http as ResponseHttp;
use Swissup\Pagespeed\Model\Optimizer\AbstractOptimizer;

class AddLinkPreload extends AbstractOptimizer
{
    private array $allowedAttributes = [
        'rel',
        'as',
        'href',
        'fetchpriority',
        'crossorigin',
        'imagesrcset',
        'imagesizes',
        'media',
        'onload',
        'onerror'
    ];

    private $assets = [];

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     *
     * @var \Swissup\Pagespeed\Model\Preload
     */
    private $preloader;

    /**
     * @var string|null
     */
    private $baseUrlHost;

    /**
     * @param Config $config
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        Config $config,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Swissup\Pagespeed\Model\Preload $preloader

    ) {
        parent::__construct($config);
        $this->storeManager = $storeManager;
        $this->preloader = $preloader;
    }

    /**
     * @return false|string|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getBaseUrlHost()
    {
        if ($this->baseUrlHost === null) {
            $baseUrl = $this->storeManager->getStore()->getBaseUrl();
            $this->baseUrlHost = $this->getHost($baseUrl);
        }

        return $this->baseUrlHost;
    }

    /**
     * @param $src string
     * @return bool
     */
    private function isThirdPartySource($src)
    {
        if (filter_var($src, FILTER_VALIDATE_URL) === false) {
            return false;
        }
        $host = $this->getHost($src);

        return $host !== $this->getBaseUrlHost();
    }

    /**
     * insteadof parse_url($url, PHP_URL_HOST)
     *
     * @param string $url
     * @return mixed
     */
    private function getHost($url)
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
             return;
        }

        $uri = \Laminas\Uri\UriFactory::factory($url);
        return $uri->getHost();
    }

    private function renderAssetItem($attributes)
    {
        $renderedAttributes = [];
        foreach ($attributes as $name => $value) {
            if (!$value || empty($value) || !in_array($name, $this->allowedAttributes)) {
                continue;
            }
            $renderedAttributes[] = $name . '="' . $value . '"';
        }
        $renderedAttributes = join(' ', $renderedAttributes);

        return '<link ' . $renderedAttributes . '/>';
    }

    /**
     * @param array|string $links
     * @param string       $as
     * @return ResponseHttp
     */
    private function renderAssetsType($links, $as = 'style')
    {
        if (is_string($links)) {
            $links = [$links];
        }

        if (empty($links)) {
            $links = [];
        }

        $extendedAttributes = [];
        $links = array_map(function($link) use (&$extendedAttributes) {
            if (isset($link['href'])) {
                $extendedAttributes[$link['href']] = $link;
            }
            return isset($link['href']) ? $link['href'] : $link;
        }, $links);

        $links = array_filter($links);
        $links = array_unique($links);
        $_html = '';
        $items = [];
        $relOffset = 2;
        $counter = 0;
        $fetchpriorityOffset = 4;
        // crossorigin="anonymous" add warning and more requests
        // but increase score. why???
//        $forceAddCrossorigin = true;
        foreach ($links as $link) {
            $href = $link;
            $rel = 'preload';
            if ($counter >= $relOffset) {
                $rel = 'prefetch';
            }
            $attributes = [
                'rel' => $rel,
                'as' => $as,
                'href' => $href,
            ];
            if (/*$forceAddCrossorigin || */$this->isThirdPartySource($href)) {
                $attributes['crossorigin'] = 'anonymous';
            }

            if ($counter < $fetchpriorityOffset) {
                $attributes['fetchpriority'] = 'high';
            }

            $imagesrcset = $imagesizes = '';
            if ($as === 'image' && isset($extendedAttributes[$link])) {
                $addition = $extendedAttributes[$link];

                foreach (['imagesrcset', 'imagesizes', 'media'] as $attributeName) {
                    if (isset($addition[$attributeName]) && !empty($addition[$attributeName])) {
                        $attributes[$attributeName] = $addition[$attributeName];
                    }
                }
            }

            $items[] = $this->renderAssetItem($attributes);

            $counter++;
        }
        $_html = implode("\n", $items);

        return $_html;
    }

    private function extractTagAttributes($tagHtml, $allowedAttributes = []) {
        $pattern = '/\b(' . implode('|', $allowedAttributes) . ')\s*=\s*"(.*?)"/';
        preg_match_all($pattern, $tagHtml, $matches, PREG_SET_ORDER);

        $attributes = [];
        foreach ($matches as $match) {
            $attributes[$match[1]] = $match[2];
        }

        return $attributes;
    }

    /**
     * Perform result postprocessing
     *
     * @param ResponseHttp $response
     * @return ResponseHttp
     */
    public function process(?ResponseHttp $response = null)
    {
        $html = $response->getBody();
        if (empty($html) || strpos($html, '<html') === false) {
            return $response;
        }
        $html = (string) $html;

        $assets = $this->preloader->getAssets();
        $preloadAssets = isset($assets['preload']) ? $assets['preload'] : [];

        $alreadyAddedPreloadAssets = [];
        preg_match_all('/<link.*rel="preload".*\/>/U', $html, $matches);
        $foundLinkPreload = $matches[0];
        foreach ($foundLinkPreload as $i => $tagHtmlString) {
            $tagAttributes = $this->extractTagAttributes($tagHtmlString, ['rel', 'as']);
            if (isset($tagAttributes['as'])) {
                $alreadyAddedPreloadAssets[$tagAttributes['as']][] = $tagHtmlString;
            } else {
                unset($foundLinkPreload[$i]);
            }
        }
        $html = str_replace($foundLinkPreload, '', $html);

        $linkRelPreloadHtml = [];
        foreach (['style', 'font', 'script', 'image'] as $as) {
            $assetHtml = '';
            if (isset($alreadyAddedPreloadAssets[$as])) {
                $assetHtml .= implode("\n", $alreadyAddedPreloadAssets[$as]);
            }
            if (isset($preloadAssets[$as])) {
                $assetHtml .= $this->renderAssetsType($preloadAssets[$as], $as);
            }
            $linkRelPreloadHtml[$as] = $assetHtml;
        }
        $linkRelPreloadHtml = implode("\n", $linkRelPreloadHtml);

        $needle = '</title>';
        $pos = strpos($html, $needle);
        if ($pos !== false) {
            $html = substr_replace($html, $needle . "\n" . $linkRelPreloadHtml, $pos, strlen($needle));
            $response->setBody($html);
        }

        return $response;
    }
}

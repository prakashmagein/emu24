<?php

namespace Swissup\Pagespeed\Model\View\Asset;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Asset\File\NotFoundException;

class PlaceholderReplacer
{
    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    private $assetRepo;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    private $localeResolver;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $url;

    /**
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Framework\UrlInterface $url
     */
    public function __construct(
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\UrlInterface $url
    ) {
        $this->assetRepo = $assetRepo;
        $this->localeResolver = $localeResolver;
        $this->url = $url;
    }

    /**
     * @param string $content
     * @return string
     */
    public function process($content)
    {
        $content = (string) $content;
        if (strpos($content, '{pagespeed_') === false) {
            return $content;
        }

        $pattern = "/\\{pagespeed_asset_url\\}\/[^\)'\"\s]+/";
        preg_match_all($pattern, $content, $matches);
        $replaces = [];
        foreach ($matches[0] as $url) {
            $path = str_replace('{pagespeed_asset_url}/', '', $url);
            $replaces[$url] = $this->assetRepo->getUrl($path);
        }

        return strtr($content, array_merge($replaces, [
            '{pagespeed_asset_url}' => $this->getAssetUrl(),
            // '{pagespeed_static_url}' => $this->getStaticUrl(),
            // '{pagespeed_locale}' => $this->getLocale(),
        ]));
    }

    /**
     * @return string
     */
    private function getAssetUrl()
    {
        $context = $this->assetRepo->getStaticViewFileContext();

        return $this->getStaticUrl() . '/'
            . $context->getAreaCode() . '/'
            . ($context->getThemePath() ? $context->getThemePath() : '') . '/'
            . $this->getLocale();
    }

    /**
     * @return string
     */
    private function getStaticUrl()
    {
        $url = $this->url->getBaseUrl([
            '_type' => \Magento\Framework\UrlInterface::URL_TYPE_STATIC
        ]);

        return str_replace(['http://', 'https://'], '//', rtrim($url, '/'));
    }

    /**
     * @return string
     */
    private function getLocale()
    {
        return $this->localeResolver->getLocale();
    }
}

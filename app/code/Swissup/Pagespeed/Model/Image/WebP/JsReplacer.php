<?php
namespace Swissup\Pagespeed\Model\Image\WebP;

use Swissup\Pagespeed\Model\Image\WebP\UrlResolver;

class JsReplacer
{
    /**
     * @var UrlResolver
     */
    private UrlResolver $urlResolver;

    public function __construct(UrlResolver $urlResolver)
    {
        $this->urlResolver = $urlResolver;
    }

    /**
     * Replace image URLs in HTML content
     *
     * @param string $html
     * @return string
     */
    public function replace(string $html): string
    {
        $html = $this->replaceInMagentoInit($html);
        $html = $this->replaceInBackgroundImageAttributes($html);
        return $html;
    }

    /**
     * Replace image URLs in JavaScript structures (e.g. x-magento-init)
     *
     * @param string $html
     * @return string
     */
    private function replaceInMagentoInit(string $html): string
    {
        $pattern = '/<script(\b[^>]*)>(.*?)<\/script>/is';
        $imgPattern = '/https?:\\\\\/\\\\\/[^\/\s]+\/\S+\.(?:jpe?g|png)/iUs';

        preg_match_all($pattern, $html, $scripts);
        $replacements = [];

        foreach ($scripts[2] as $i => $scriptContent) {
            $scriptTagAttrs = $scripts[1][$i];
            if (strpos($scriptTagAttrs, 'x-magento-init') === false) {
                continue;
            }

            preg_match_all($imgPattern, $scriptContent, $matches);
            foreach ($matches[0] as $originalEscapedUrl) {
                $originalUrl = str_replace('\/', '/', $originalEscapedUrl);
                $webpUrl = $this->urlResolver->resolve($originalUrl);

                if ($webpUrl !== null) {
                    $webpEscapedUrl = str_replace('/', '\/', $webpUrl);
                    $replacements[$originalEscapedUrl] = $webpEscapedUrl;
                }
            }
        }

        if (!empty($replacements)) {
            $html = str_replace(array_keys($replacements), array_values($replacements), $html);
        }

        return $html;
    }

    /**
     * Replace URLs in data-background-images attribute content with WebP versions
     *
     * @param string $html
     * @return string
     */
    private function replaceInBackgroundImageAttributes(string $html): string
    {
        $pattern = '/data-background-images=(?:\'|"){.+?}(?:\'|")/';
        $imgPattern = '/https?:\/\/[^\/\s]+\/\S+\.(?:jpe?g|png)/i';

        $replacements = [];

        preg_match_all($pattern, $html, $attributes);

        foreach ($attributes[0] as $attribute) {
            preg_match_all($imgPattern, $attribute, $urls);

            foreach ($urls[0] as $originalUrl) {
                $webpUrl = $this->urlResolver->resolve($originalUrl);
                if ($webpUrl !== null) {
                    $replacements[$originalUrl] = $webpUrl;
                }
            }
        }

        if (!empty($replacements)) {
            $html = str_replace(array_keys($replacements), array_values($replacements), $html);
        }

        return $html;
    }

}

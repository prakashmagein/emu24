<?php
namespace Swissup\Pagespeed\Model\Image\WebP;

use DOMElement;
use Swissup\Pagespeed\Model\Image\File;
use Swissup\Pagespeed\Model\Image\ImageAttributeParser;

class PictureTagBuilder
{
    private UrlResolver $webpUrlResolver;
    private File $fileIo;
    private ImageAttributeParser $imageAttributeParser;

    public function __construct(
        UrlResolver $webpUrlResolver,
        File $fileIo,
        ImageAttributeParser $imageAttributeParser
    ) {
        $this->webpUrlResolver = $webpUrlResolver;
        $this->fileIo = $fileIo;
        $this->imageAttributeParser = $imageAttributeParser;
    }

    public function build(DOMElement $node): string
    {
        $imageUrls = [];
        foreach ($this->imageAttributeParser->getSupportedAttributeNames() as $attrName) {
            $attrValue = $node->getAttribute($attrName);
            if (empty($attrValue)) {
                continue;
            }
            $items = explode(',', $attrValue);
            $urls = array_map(function ($item) {
                $item = trim($item);
                list($url, ) = explode(' ', $item, 2);
                return $url;
            }, $items);

            array_push($imageUrls, ...$urls);
        }

        $imageUrls = array_filter($imageUrls);
        $imageUrls = array_unique($imageUrls);

        $webPUrls = array_map([$this->webpUrlResolver, 'resolve'], $imageUrls);
        $webPUrls = array_replace($imageUrls, array_filter($webPUrls));

        $pictureHtml = "<picture>\n"; // ПОЧАТОК <picture> ТЕГУ

        $attributes = $this->imageAttributeParser->getSupportedAttributeNames();
        $attributes[] = 'sizes';

        // Add WebP source
        $sourceWebP = [];
        $this->sourceAttrSet($sourceWebP, 'type', 'image/webp');
        foreach ($attributes as $attrName) {
            if ($attrValue = $node->getAttribute($attrName)) {
                $attrValue = str_replace($imageUrls, $webPUrls, $attrValue);
                $this->sourceAttrSet($sourceWebP, $attrName, $attrValue);
            }
        }
        $pictureHtml .= $this->sourceRender($sourceWebP);

        // Add original image source as fallback
        $extension = $this->fileIo->getFileExtensionFromUrl(reset($imageUrls));
        $sourceOriginal = [];
        $this->sourceAttrSet($sourceOriginal, 'type', "image/{$extension}");
        foreach ($attributes as $attrName) {
            if ($attrValue = $node->getAttribute($attrName)) {
                $this->sourceAttrSet($sourceOriginal, $attrName, $attrValue);
            }
        }
        $pictureHtml .= $this->sourceRender($sourceOriginal);

        $pictureHtml .= "</picture>\n"; // ЗАКІНЧЕННЯ <picture> ТЕГУ

        return $pictureHtml;
    }

    private function sourceAttrSet(array &$source, string $attr, string $value): void
    {
        $map = [
            'src' => 'srcset',
            'data-src' => 'data-srcset'
        ];
        $source[$map[$attr] ?? $attr] = $value;
    }

    private function sourceRender(array $source): string
    {
        array_walk($source, function (&$value, $attr) {
            $value = "{$attr}=\"{$value}\"";
        });
        return '<source ' . implode(' ', $source) . " />\n";
    }
}

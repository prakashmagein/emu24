<?php
namespace Swissup\Pagespeed\Model\Image;

class ImageAttributeParser
{
    /**
     * @var string[]
     */
    private array $supportedAttributes = ['src', 'srcset'];

    /**
     * Returns an array of attribute names that contain image URLs this parser handles.
     *
     * @return string[]
     */
    public function getSupportedAttributeNames(): array
    {
        return $this->supportedAttributes;
    }

    /**
     * Parses the value of an image attribute (like 'src' or 'srcset')
     * and returns an array of individual image URLs found within it.
     *
     * @param string $attrName The name of the attribute (e.g., 'src', 'srcset').
     * @param string $attrValue The value of the attribute.
     * @return string[] An array of extracted image URLs.
     */
    public function parseAttributeValue(string $attrName, string $attrValue): array
    {
        switch ($attrName) {
            case 'srcset':
                return $this->parseSrcset($attrValue);
            case 'src':
                $trimmedValue = trim($attrValue);
                return $trimmedValue ? [$trimmedValue] : [];
            default:
                return []; // Or throw an exception for unsupported attributes
        }
    }

    /**
     * Parses a 'srcset' string and returns an array of image URLs.
     * This method combines the logic of parseSrcsetSafely and parseSrcsetFallback.
     *
     * @param string $srcset The 'srcset' attribute value.
     * @return string[] An array of extracted image URLs.
     */
    private function parseSrcset(string $srcset): array
    {
        $urls = [];
        $srcset = trim($srcset);

        if (empty($srcset)) {
            return $urls;
        }

        // Main regex pattern for srcset (URL followed by descriptor)
        // e.g., "image.jpg 647w, image@2x.jpg 2x"
        $pattern = '/([^\s,]+(?:,[^\s]*)*?)\s+(\d+[wx]|[\d.]+x)(?:\s*,\s*|$)/';

        if (preg_match_all($pattern, $srcset . ',', $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $urls[] = trim($match[1]); // Extract only the URL part
            }
        } else {
            // Fallback logic for simpler or malformed srcset values
            // Split by comma, then take the first part (URL) before any space
            $parts = preg_split('/\s*,\s*/', $srcset);
            foreach ($parts as $part) {
                $part = trim($part);
                if (empty($part)) {
                    continue;
                }
                list($imageUrl) = explode(' ', $part, 2); // Take only the part before the first space
                if ($imageUrl) {
                    $urls[] = $imageUrl;
                }
            }
        }

        return $urls;
    }
}

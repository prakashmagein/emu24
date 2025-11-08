<?php
namespace Swissup\Pagespeed\Model\Optimizer\Image;

use Swissup\Pagespeed\Helper\Config;
use Swissup\Pagespeed\Model\Optimizer\AbstractCachableOptimizer;

abstract class AbstractImage extends AbstractCachableOptimizer
{
    /**
     *
     * @param  string $html
     * @return array
     */
    protected function getImagesFromHtml($html)
    {
        $_html = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $html);
        $images = [];
        preg_match_all('/<img[\s\r\n]+.*?>/is', (string) $_html, $images);
        unset($_html);
        $images = isset($images[0]) ? $images[0] : [];

        return $images;
    }

    protected function getDOMNodeFromImageHtml($imageHTML)
    {
        $dom = new \DOMDocument();

        $oldUseErrors = libxml_use_internal_errors(true);
        $encoding = mb_detect_encoding($imageHTML, 'auto');
        if ($encoding === 'UTF-8') {
            $imageHTML = "\xEF\xBB\xBF" . $imageHTML;
        }
        if ($encoding) {
            $dom->loadHTML('<?xml encoding="' . $encoding . '">' . $imageHTML);
        } else {
            $dom->loadHTML($imageHTML);
        }
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);
        $nodeList = $xpath->query('//img');
        libxml_use_internal_errors($oldUseErrors);

        return $nodeList->length > 0 ? $nodeList->item(0) : null;
    }

    /**
     *
     * @param  \DOMNode $node
     * @return string
     */
    protected function getImageHtmlFromDOMNode($node)
    {
        // $imageHtml = $node->C14N();
        $clonedNode = $node->cloneNode(true);
        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        // silence warnings and erros during parsing.
        // More at http://php.net/manual/en/function.libxml-use-internal-errors.php
        $oldUseErrors = libxml_use_internal_errors(true);
        $dom->appendChild($dom->importNode($clonedNode, true));
        $imageHtml = $dom->saveHTML();
        // restore old value
        libxml_use_internal_errors($oldUseErrors);
        $imageHtml = mb_convert_encoding($imageHtml, 'ISO-8859-1', 'UTF-8');

        $imageHtml = html_entity_decode($imageHtml);
        // $imageHtml = $this->getUtf8ToHtml($imageHtml);
        $imageHtml = str_replace('></img>', ' />', $imageHtml);

        return $imageHtml;
    }
}

<?php
namespace Swissup\Amp\Model\Html\Filter\Dom;

class ProhibitedTags extends DomAbstract
{
    /**
     * Removes prohibited tags from the DOMDocument
     * @see https://github.com/ampproject/amphtml/blob/master/spec/amp-html-format.md#html-tags
     *
     * @param  \DOMDocument $document
     * @return void
     */
    public function process($document)
    {
        $tags = [
            'base',
            'frame',
            'frameset',
            'object',
            'param',
            'applet',
            'embed',
        ];
        foreach ($tags as $tag) {
            $remove = [];
            $nodes = $document->getElementsByTagName($tag);
            foreach ($nodes as $node) {
                $remove[] = $node;
            }
            foreach ($remove as $node) {
                $node->parentNode->removeChild($node);
            }
        }
    }
}

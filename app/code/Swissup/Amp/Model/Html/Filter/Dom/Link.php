<?php
namespace Swissup\Amp\Model\Html\Filter\Dom;

class Link extends DomAbstract
{
    /**
     * Remove link tags without rel attribute
     *
     * @param  \DOMDocument $document
     * @return void
     */
    public function process($document)
    {
        $remove = [];
        $nodes = $document->getElementsByTagName('link');
        foreach ($nodes as $node) {
            if ($node->hasAttribute('rel') && $node->getAttribute('rel')) {
                continue;
            }
            $remove[] = $node;
        }

        foreach ($remove as $node) {
            $node->parentNode->removeChild($node);
        }
    }
}

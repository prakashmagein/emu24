<?php
namespace Swissup\Amp\Model\Html\Filter\Dom;

class Script extends DomAbstract
{
    /**
     * Removes <script> tags from the DOMDocument
     *
     * @param  \DOMDocument $document
     * @return void
     */
    public function process($document)
    {
        $remove = [];
        $nodes = $document->getElementsByTagName('script');
        foreach ($nodes as $node) {
            // allow amp scripts
            if ($node->hasAttribute('custom-element')
                || strpos($node->getAttribute('src'), 'ampproject') !== false
                || $node->getAttribute('type') === 'application/ld+json'
                || $node->getAttribute('type') === 'application/json') {

                continue;
            }

            $remove[] = $node;
        }
        foreach ($remove as $node) {
            $node->parentNode->removeChild($node);
        }
    }
}

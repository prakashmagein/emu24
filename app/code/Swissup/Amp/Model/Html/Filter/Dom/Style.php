<?php
namespace Swissup\Amp\Model\Html\Filter\Dom;

class Style extends DomAbstract
{
    /**
     * Removes <style> tags from the DOMDocument
     *
     * @param  \DOMDocument $document
     * @return void
     */
    public function process($document)
    {
        $remove = [];
        $nodes = $document->getElementsByTagName('style');
        foreach ($nodes as $node) {
            // allow amp styles
            if ($node->hasAttribute('amp-boilerplate')
                || $node->hasAttribute('amp-custom')) {

                continue;
            }

            $remove[] = $node;
        }
        foreach ($remove as $node) {
            $node->parentNode->removeChild($node);
        }
    }
}

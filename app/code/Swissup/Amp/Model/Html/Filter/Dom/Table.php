<?php
namespace Swissup\Amp\Model\Html\Filter\Dom;

class Table extends DomAbstract
{
    /**
     * Wrap tables into table-responsive wrapper
     *
     * @param  \DOMDocument $document
     * @return void
     */
    public function process($document)
    {
        $remove = [];
        $nodes = $document->getElementsByTagName('table');
        foreach ($nodes as $node) {
            $parentClass = $node->parentNode->getAttribute('class');
            if (false !== strpos($parentClass, 'table-responsive')) {
                continue;
            }

            $wrapper = $document->createElement('div');
            $wrapper->setAttribute('class', 'table-responsive');
            $node->parentNode->insertBefore($wrapper, $node);
            $wrapper->appendChild($node);
        }
    }
}

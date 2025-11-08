<?php
namespace Swissup\Amp\Model\Html\Filter\Dom;

class Iframe extends DomAbstract
{
    /**
     * 1. Replace unsupported iframe tags with amp-iframe
     *
     * @param  \DOMDocument $document
     * @return void
     */
    public function process($document)
    {
        $replace = [];
        $nodes = $document->getElementsByTagName('iframe');
        foreach ($nodes as $node) {
            $replace[] = $node;
        }

        foreach ($replace as $node) {
            $img = $document->createElement('amp-iframe');
            $img->setAttribute('layout', 'responsive');
            $img->setAttribute('frameborder', 0);
            $img->setAttribute('sandbox', 'allow-scripts allow-popups allow-same-origin');
            foreach ($this->getNodeAttributes($node) as $key => $value) {
                $img->setAttribute($key, $value);
            }
            $node->parentNode->replaceChild($img, $node);
        }

        if (count($replace)) {
            $this->addAmpComponent(
                'amp-iframe',
                'https://cdn.ampproject.org/v0/amp-iframe-0.1.js'
            );
        }
    }
}

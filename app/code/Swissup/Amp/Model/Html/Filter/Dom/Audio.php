<?php
namespace Swissup\Amp\Model\Html\Filter\Dom;

class Audio extends DomAbstract
{
    /**
     * Replace unsupported audio tags with amp-audio
     *
     * @param  \DOMDocument $document
     * @return void
     */
    public function process($document)
    {
        $replace = [];
        $nodes = $document->getElementsByTagName('audio');
        foreach ($nodes as $node) {
            $replace[] = $node;
        }

        foreach ($replace as $node) {
            $audio = $document->createElement('amp-audio');
            $audio->setAttribute('layout', 'fixed');
            foreach ($this->getNodeAttributes($node) as $key => $value) {
                $audio->setAttribute($key, $value);
            }

            foreach ($node->childNodes as $childNode) {
                if ($childNode->nodeType !== XML_ELEMENT_NODE) {
                    continue;
                }

                $source = $document->createElement($childNode->tagName);
                foreach ($this->getNodeAttributes($childNode) as $key => $value) {
                    $source->setAttribute($key, $value);
                }
                $audio->appendChild($source);
            }

            $node->parentNode->replaceChild($audio, $node);
        }

        if (count($replace)) {
            $this->addAmpComponent(
                'amp-audio',
                'https://cdn.ampproject.org/v0/amp-audio-0.1.js'
            );
        }
    }
}

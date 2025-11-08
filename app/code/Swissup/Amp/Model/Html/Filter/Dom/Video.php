<?php
namespace Swissup\Amp\Model\Html\Filter\Dom;

class Video extends DomAbstract
{
    /**
     * Replace unsupported video tags with amp-video
     *
     * @param  \DOMDocument $document
     * @return void
     */
    public function process($document)
    {
        $replace = [];
        $nodes = $document->getElementsByTagName('video');
        foreach ($nodes as $node) {
            $replace[] = $node;
        }

        foreach ($replace as $node) {
            $video = $document->createElement('amp-video');
            $video->setAttribute('layout', 'responsive');
            foreach ($this->getNodeAttributes($node) as $key => $value) {
                $video->setAttribute($key, $value);
            }

            foreach ($node->childNodes as $childNode) {
                if ($childNode->nodeType !== XML_ELEMENT_NODE) {
                    continue;
                }

                $source = $document->createElement($childNode->tagName);
                foreach ($this->getNodeAttributes($childNode) as $key => $value) {
                    $source->setAttribute($key, $value);
                }
                $video->appendChild($source);
            }

            $node->parentNode->replaceChild($video, $node);
        }

        if (count($replace)) {
            $this->addAmpComponent(
                'amp-video',
                'https://cdn.ampproject.org/v0/amp-video-0.1.js'
            );
        }
    }
}

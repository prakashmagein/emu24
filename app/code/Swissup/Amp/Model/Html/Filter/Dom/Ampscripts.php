<?php
namespace Swissup\Amp\Model\Html\Filter\Dom;

class Ampscripts extends DomAbstract
{
    /**
     * Dynamically add amp component scripts
     *
     * @param  \DOMDocument $document
     * @return void
     */
    public function process($document)
    {
        $head = $document->getElementsByTagName('head')->item(0);
        $xpath = new \DOMXPath($document);
        $meta = $xpath->query('.//meta[@name="amp-scripts"]', $head)->item(0);
        if (!$meta || !$this->getScriptsBlock()) {
            return;
        }

        $scripts = $this->getScriptsBlock()->getItems();
        foreach ($scripts as $type => $values) {
            $script = $document->createElement('script');
            if ($values['async']) {
                $script->setAttribute('async', '');
            }
            if ($type === 'amp-mustache') {
                $script->setAttribute('custom-template', $type);
            } else {
                $script->setAttribute('custom-element', $type);
            }
            $script->setAttribute('src', $values['src']);
            $meta->parentNode->insertBefore($script, $meta);
        }

        $meta->parentNode->removeChild($meta);
    }
}

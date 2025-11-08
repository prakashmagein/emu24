<?php
namespace Swissup\Amp\Model\Html\Filter\Dom;

class Button extends DomAbstract
{
    /**
     * Replace <button onclick=setLocation..> with <a href=..>
     *
     * @param  \DOMDocument $document
     * @return void
     */
    public function process($document)
    {
        $replace = [];
        $xpath = new \DOMXPath($document);
        $nodes = $xpath->query('.//button[@onclick]', $document);
        foreach ($nodes as $node) {
            $replace[] = $node;
        }

        foreach ($replace as $node) {
            if (!$location = $this->getLocation($node)) {
                continue;
            }

            $link = $document->createElement('a');
            $link->setAttribute('href', $location);
            $link->textContent = $node->textContent;
            foreach ($this->getNodeAttributes($node) as $key => $value) {
                if (in_array($key, ['type', 'onclick'])) {
                    continue;
                }
                $link->setAttribute($key, $value);
            }
            $node->parentNode->replaceChild($link, $node);
        }
    }

    /**
     * Get button onclick location
     *
     * @param  \NodeElement $node
     * @return mixed
     */
    protected function getLocation($node)
    {
        $onclick = $node->getAttribute('onclick');
        if (strpos($onclick, 'setLocation') !== false) {
            preg_match('/setLocation\([\'"](.+)?[\'"]\)/', $onclick, $matches);
            if (!empty($matches[1])) {
                return $matches[1];
            }
        }

        return false;
    }
}

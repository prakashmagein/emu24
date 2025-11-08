<?php
namespace Swissup\Amp\Model\Html\Filter\Dom;

class Ampstyles extends DomAbstract
{
    /**
     * Dynamically add amp styles
     *
     * @param  \DOMDocument $document
     * @return void
     */
    public function process($document)
    {
        $head = $document->getElementsByTagName('head')->item(0);
        $xpath = new \DOMXPath($document);
        $style = $xpath->query('.//style[@amp-custom]', $head)->item(0);
        if (!$style || !$this->getStylesBlock()) {
            return;
        }

        if (!empty($style->textContent)) {
            return;
        }

        $style->appendChild(
            $document->createTextNode(
                $this->getStylesBlock()->toHtml()
            )
        );
    }
}

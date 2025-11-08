<?php
namespace Swissup\Amp\Model\Html\Filter\Dom;

class Attribute extends DomAbstract
{
    /**
     * 1. Removes js attributes from the DOMDocument nodes
     * 2. Add `data-amp-` attributes
     *
     * @param  \DOMDocument $document
     * @return void
     */
    public function process($document)
    {
        $blacklist = [
            'nowrap',
            'style',
            'price',
            'border',
            'noshade',
            'aria-labeledby',
            'loading'
        ];
        // whitelisted attributes for specific tags only
        $whitelist = [
            'align' => [
                'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
                'p', 'blockquote', 'div',
                'table', 'tr', 'td', 'th'
            ],
            'border' => ['table', 'a', 'img'],
            'size' => ['input', 'select'],
        ];

        $nodesToRemove = [];
        $nodes = $document->getElementsByTagName('*');
        foreach ($nodes as $node) {
            if ($this->hasAttribute($node, 'data-swissupamp-remove', '!')) {
                $nodesToRemove[] = $node;
                continue;
            }

            // 1. Remove blacklisted attributes
            $remove = [];
            foreach ($node->attributes as $attributeName => $attribute) {
                if (strlen($attributeName) > 2
                    && substr($attributeName, 0, 2) === 'on'
                ) {
                    $remove[] = $attributeName;
                }

                // remove inline width
                if ($attributeName === 'width'
                    && strpos($node->nodeName, 'amp-') === false
                    && !in_array($node->nodeName, ['audio', 'iframe', 'img', 'video'])
                ) {
                    $remove[] = $attributeName;
                }

                if (isset($whitelist[$attributeName])) {
                    if (!in_array($node->nodeName, $whitelist[$attributeName])) {
                        $remove[] = $attributeName;
                    }
                }

                if (in_array($attributeName, $blacklist)) {
                    $remove[] = $attributeName;
                }
            }
            $this->removeAttributes($node, $remove);

            // 2. Convert `data-swissupamp-` attributes
            foreach ($this->getAmpAttributes($node) as $key => $value) {
                switch ($key) {
                    case 'class-append':
                        $oldClass = $node->getAttribute('class');
                        $node->setAttribute('class', $oldClass . ' ' . $value);
                        break;
                    case 'remove':
                        $this->removeAttributes($node, explode(',', $value));
                        break;
                    default:
                        $node->setAttribute($key, $value);
                }
            }
        }

        foreach ($nodesToRemove as $node) {
            $node->parentNode->removeChild($node);
        }
    }
}

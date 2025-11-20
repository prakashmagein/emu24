<?php
/**
 * Custom Dom implementation to guard against invalid XPath expressions during config merges.
 */
declare(strict_types=1);

namespace Emu24\ConfigDomFix\Config;

use Magento\Framework\Config\Dom as BaseDom;

class Dom extends BaseDom
{
    /**
     * Safely retrieve the first matched node for the provided XPath.
     *
     * @param string $path
     * @return \DOMNode|null
     */
    protected function _getMatchedNode($path)
    {
        $matches = $this->safeQuery($path);
        return $matches[0] ?? null;
    }

    /**
     * Merge node keeping compatibility with parent behaviour while avoiding invalid XPath warnings.
     *
     * @param \DOMElement $node
     * @param string $parentPath
     * @return void
     */
    protected function _mergeNode(\DOMElement $node, $parentPath)
    {
        $path = $this->_getNodePathByParent($node, $parentPath);
        $matchedNodes = $this->safeQuery($path);

        if ($matchedNodes) {
            if (!$node->hasChildNodes()) {
                $parentMatchedNode = $this->_getMatchedNode($parentPath);
                if ($parentMatchedNode) {
                    $newNode = $this->dom->importNode($node, true);
                    $parentMatchedNode->appendChild($newNode);
                }

                return;
            }

            foreach ($node->childNodes as $childNode) {
                if ($childNode instanceof \DOMElement) {
                    $this->_mergeNode($childNode, $path);
                }
            }
        } else {
            $parentMatchedNode = $this->_getMatchedNode($parentPath);
            if ($parentMatchedNode) {
                $newNode = $this->dom->importNode($node, true);
                $parentMatchedNode->appendChild($newNode);
            }
        }
    }

    /**
     * Query DOM without emitting warnings for malformed XPath expressions.
     *
     * @param string $nodePath
     * @return array<int, \DOMElement>
     */
    private function safeQuery(string $nodePath): array
    {
        $xPath = $this->_getDomXPath();

        try {
            $matchedNodes = @$xPath->query($nodePath);
        } catch (\DOMException $exception) {
            $matchedNodes = false;
        }

        if (!$matchedNodes) {
            return [];
        }

        $nodes = [];
        foreach ($matchedNodes as $matchedNode) {
            if ($matchedNode instanceof \DOMElement) {
                $nodes[] = $matchedNode;
            }
        }

        return $nodes;
    }
}

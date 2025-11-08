<?php
namespace Swissup\Amp\Model\Html\Filter\Dom;

abstract class DomAbstract
{
    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $formKey;

    /**
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     */
    public function __construct(
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Framework\Data\Form\FormKey $formKey
    ) {
        $this->layout = $layout;
        $this->formKey = $formKey;
    }

    /**
     * Extract non-`data-swissupamp-` attributes from node element
     *
     * @param  \NodeElement $node
     * @return array
     */
    public function getNodeAttributes($node)
    {
        $result = [];
        foreach ($this->getAllAttributes($node) as $name => $value) {
            if (strpos($name, 'data-swissupamp-') === 0) {
                continue;
            }
            $result[$name] = $value;
        }

        return $result;
    }

    /**
     * Extract `data-swissupamp-` attributes from node element
     *
     * @param  \NodeElement $node
     * @return array
     */
    public function getAmpAttributes($node)
    {
        $result = [];
        foreach ($this->getAllAttributes($node) as $name => $value) {
            if (strpos($name, 'data-swissupamp-') !== 0) {
                continue;
            }
            $name = str_replace('data-swissupamp-', '', $name);
            $result[$name] = $value;
        }

        return $result;
    }

    /**
     * Get all node attributes as key => value pairs
     *
     * @param  NodeElement $node
     * @return array
     */
    public function getAllAttributes($node)
    {
        $result = [];
        foreach ($node->attributes as $attribute) {
            $attributeName = $this->getAttributeName($attribute->name);
            if (!$attributeName) {
                continue;
            }
            $result[$attributeName] = $attribute->value;
        }

        return $result;
    }

    /**
     * @param  \NodeElement  $node
     * @param  string  $attribute
     * @param  string $value
     * @return boolean
     */
    public function hasAttribute($node, $attribute, $value = false)
    {
        $hasAttribute = $node->hasAttribute($attribute);

        if ($value === false) {
            return $hasAttribute;
        }

        return $value === $node->getAttribute($attribute);
    }

    /**
     * @param  \NodeElement $node
     * @param  array $names
     */
    protected function removeAttributes($node, array $names)
    {
        foreach ($names as $name) {
            if ($name === '*') {
                return $this->removeAttributes(
                    $node,
                    array_keys($this->getNodeAttributes($node))
                );
            }

            if (!$node->hasAttribute($name)) {
                continue;
            }
            $node->removeAttribute($name);
        }
    }

    /**
     * Add amp component script into swissupamp.script block
     *
     * @param string  $type  amp-element
     * @param string  $src   js src
     * @param boolean $async async
     */
    public function addAmpComponent($type, $src, $async = true)
    {
        if ($this->getScriptsBlock()) {
            $this->getScriptsBlock()->addItem($type, $src, $async);
        }
    }

    /**
     * Retrieve swissupamp.scripts block from layout
     *
     * @return \Swissup\Amp\Block\Js
     */
    public function getScriptsBlock()
    {
        return $this->layout->getBlock('swissupamp.scripts');
    }

    /**
     * Retrieve swissupamp.styles block from layout
     *
     * @return \Swissup\Amp\Block\Scss
     */
    public function getStylesBlock()
    {
        return $this->layout->getBlock('swissupamp.styles');
    }

    /**
     * Use this mathod to handle attribute name mapping.
     *
     * Example:
     *     data-desktop-attribute => amp-attribute
     *     dektop-attribute       => false // - will be removed in AMP
     *
     * @param  string $name
     * @return string
     */
    protected function getAttributeName($name)
    {
        return $name;
    }
}

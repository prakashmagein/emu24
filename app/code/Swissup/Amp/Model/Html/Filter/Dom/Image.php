<?php
namespace Swissup\Amp\Model\Html\Filter\Dom;

class Image extends DomAbstract
{
    /**
     * @var \Swissup\Amp\Helper\Image
     */
    protected $imageHelper;

    /**
     * @param \Swissup\Amp\Helper\Image $imageHelper
     */
    public function __construct(
        \Swissup\Amp\Helper\Image $imageHelper
    ) {
        $this->imageHelper = $imageHelper;
    }

    /**
     * 1. Replace unsupported img tags with amp-img
     * 2. Remove images without required height attribute
     *
     * @param  \DOMDocument $document
     * @return void
     */
    public function process($document)
    {
        $replace = [];
        $nodes = $document->getElementsByTagName('img');
        foreach ($nodes as $node) {
            $replace[] = $node;
        }

        // Fix for luma-based themes
        $attributeMapping = [
            'max-width' => 'width',
            'max-height' => 'height',
        ];
        foreach ($replace as $node) {
            if (!$src = $this->getSrc($node)) {
                $node->parentNode->removeChild($node);
                continue;
            }

            foreach ($attributeMapping as $from => $to) {
                if (!$node->hasAttribute($from)) {
                    continue;
                }
                if (!$node->hasAttribute($to)) {
                    $node->setAttribute($to, $node->getAttribute($from));
                }
                $node->removeAttribute($from);
            }

            // detect image dimensions in a runtime, if needed
            foreach (['width', 'height'] as $attribute) {
                if ($node->getAttribute($attribute)) {
                    continue;
                }

                $method = 'get' . ucfirst($attribute);
                $value  = $this->imageHelper->{$method}($src);

                if (!$value) {
                    $node->parentNode->removeChild($node);
                    continue 2;
                }
                $node->setAttribute($attribute, $value);
            }

            $img = $document->createElement('amp-img');
            $img->setAttribute('layout', 'intrinsic');
            $img->setAttribute('src', $src);
            if ($srcset = $this->getSrcset($node)) {
                $img->setAttribute('srcset', $srcset);
            }
            foreach ($this->getNodeAttributes($node) as $key => $value) {
                if (in_array($key, ['src', 'srcset'])) {
                    continue;
                }
                $img->setAttribute($key, $value);
            }
            $node->parentNode->replaceChild($img, $node);
        }
    }

    /**
     * Get image src value
     *
     * @param  \NodeElement $node
     * @return mixed
     */
    protected function getSrc($node)
    {
        foreach ($this->getSrcAttributes() as $name) {
            if ($src = $node->getAttribute($name)) {
                return $src;
            }
        }

        return false;
    }

    /**
     * Get image srcset value
     *
     * @param  \NodeElement $node
     * @return mixed
     */
    protected function getSrcset($node)
    {
        foreach (['srcset', 'data-srcset'] as $name) {
            if ($srcset = $node->getAttribute($name)) {
                return $srcset;
            }
        }

        return false;
    }

    /**
     * Get popuplar names for src attribute
     *
     * @return array
     */
    protected function getSrcAttributes()
    {
        return [
            'src',
            'data-src',
            'data-lazy',
        ];
    }
}

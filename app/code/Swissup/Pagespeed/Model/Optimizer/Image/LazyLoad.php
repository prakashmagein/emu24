<?php
namespace Swissup\Pagespeed\Model\Optimizer\Image;

use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\App\Response\Http as ResponseHttp;
use Swissup\Pagespeed\Helper\Config;

class LazyLoad extends AbstractImage
{
    /**
     *
     * @var array
     */
    private $ignores;

    /**
     *
     * @return array
     */
    private function getIgnoreWithAttributes()
    {
        return [
            'class' => ['gallery-placeholder__image', 'product-image-photo']
        ];
    }

    /**
     *
     * @param \DOMElement $node
     * @return boolean
     */
    private function shouldIgnore($node)
    {
        $attributes = $this->getIgnoreWithAttributes();
        foreach ($attributes as $attribute => $fingerprints) {
            $_hasAttribute = $node->hasAttribute($attribute);
            if (!$_hasAttribute) {
                continue;
            }
            $attributeValue = $node->getAttribute($attribute);
            if (!is_array($fingerprints)) {
                $fingerprints = [$fingerprints];
            }
            if (in_array($attributeValue, $fingerprints)) {
                return true;
            }
            foreach ($fingerprints as $fingerprint) {
                if (false !== strstr($attributeValue, $fingerprint)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     *
     * @param string $src
     * @return boolean
     */
    private function shouldIgnoreBySrc($src)
    {
        if ($this->ignores === null) {
            $this->ignores = $this->config->getLazyloadIgnores();
        }
        foreach ($this->ignores as $ignore) {
            if (false !== strstr($src, $ignore)) {
                return true;
            }
        }
        return false;
    }

    /**
     *
     * @return int
     */
    protected function getOffset()
    {
        $offset = $this->config->isMobile() ?
            $this->config->getLazyloadMobileOffset() : $this->config->getLazyloadOffset();
        $offset = $offset + 1;

        return $offset;
    }

    /**
     * Perform result postprocessing
     *
     * @param ResponseHttp $response
     * @return ResponseHttp
     */
    public function process(?ResponseHttp $response = null)
    {
         if (!$this->config->isImageLazyLoadEnable() || $response === null) {
             return $response;
         }
        $html = $response->getBody();
        if (empty($html) || strpos($html, '<html') === false) {
            return $response;
        }
        $images = $this->getImagesFromHtml($html);
        if (empty($images)) {
            return $response;
        }
        $offset = $this->getOffset();

        foreach ($images as $image) {
            /** @var \DOMElement $node */
            $node = $this->getDOMNodeFromImageHtml($image);
            if (!$node->hasAttribute('src')) {
                continue;
            }
            $src = $node->getAttribute('src');
            if (empty($src)
                || $node->hasAttribute('loading')
                || $this->shouldIgnoreBySrc($src)
                || $this->shouldIgnore($node)
            ) {
                continue;
            }

            $offset = $offset - 1;
            if ($offset > 0) {
                continue;
            }

            // lazyload all except first X number of images
            $node->setAttribute('loading', 'lazy');
            $lazyloadImage = $this->getImageHtmlFromDOMNode($node);
            $html = str_replace($image, $lazyloadImage, $html);
        }

        $response->setBody($html);

        return $response;
    }
}

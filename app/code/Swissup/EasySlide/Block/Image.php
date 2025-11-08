<?php

namespace Swissup\EasySlide\Block;

use Magento\Framework\View\Element\Template;

class Image extends Template
{
    /**
     * {@inheritdoc}
     */
    protected $_template = 'image.phtml';

    /**
     * @var \Swissup\EasySlide\Helper\Image
     */
    protected $helper;

    /**
     * @param \Swissup\EasySlide\Helper\Image $helper
     * @param Template\Context                $context
     * @param array                           $data
     */
    public function __construct(
        \Swissup\EasySlide\Helper\Image $helper,
        Template\Context $context,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * Render image html
     *
     * @param  array  $slide
     * @param  bool   $isLazy
     * @return string
     */
    public function render(array $slide, $isLazy)
    {
        $this->assign('isLazy', $isLazy);
        $this->assign('slide', $slide);
        $this->assign(
            'attributes',
            $this->buildAttributes(
                $slide['image'],
                $slide['sizes'] ?? '',
                $slide['widths'] ?? ''
            )
        );
        return $this->toHtml();
    }

    /**
     * Resize image using easyslide helper
     *
     * @param  string   $imageFile
     * @param  int      $w
     * @param  null|int $h
     * @return string|boolean
     */
    public function resize($imageFile, $w, $h = null)
    {
        return $this->helper->resize($imageFile, $w, $h);
    }

    /**
     * Build `srcset` and `sizes` attributes for image
     *
     * @param  string       $imageFile
     * @param  string|array $sizes
     * @param  string|array $widths    Widths for scrset attribute
     * @return array
     */
    public function buildAttributes(
        string $imageFile,
        $sizes,
        $widths
    ): array {
        if (!$sizes && !$widths) {
            return [];
        }

        $originalWidth = $this->getImageWidth($imageFile);
        $srcsetAttr = [];
        if (!is_array($widths)) {
            $widths = explode(',', $widths);
            $widths = array_map(function ($width) { return trim($width); }, $widths);
        }

        foreach ($widths as $width) {
            if (strpos($width, 'w') !== false) {
                $width = str_replace('w', '', $width);
                if (!$width || $width >= $originalWidth) {
                    continue;
                }

                $srcsetAttr[] = "{$this->resize($imageFile, $width)} {$width}w";
            }

        }
        $srcsetAttr[] = "{$this->getImageUrl($imageFile)} {$originalWidth}w";

        return [
            'srcset' => implode(', ', $srcsetAttr),
            'sizes' => is_array($sizes) ? implode(', ', $sizes) : $sizes
        ];
    }

    /**
     * Get image URL
     *
     * @param  string $imageFile
     * @return string
     */
    public function getImageUrl($imageFile)
    {
        return $this->helper->getBaseUrl() . $imageFile;
    }

    /**
     * @param string $imageFile
     * @return int
     */
    public function getImageWidth($imageFile)
    {
        return $this->helper->getImageWidth($imageFile);
    }

    /**
     * @param string $imageFile
     * @return int
     */
    public function getImageHeight($imageFile)
    {
        return $this->helper->getImageHeight($imageFile);
    }
}

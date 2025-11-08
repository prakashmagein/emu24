<?php

namespace Swissup\Amp\Helper;

class Image extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Swissup\Image\Helper\Dimensions
     */
    private $imageDimensions;

    /**
     * @param \Swissup\Image\Helper\Dimensions $imageDimensions
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Swissup\Image\Helper\Dimensions $imageDimensions
    ) {
        $this->imageDimensions = $imageDimensions;
        parent::__construct($context);
    }

    /**
     * Get image width
     *
     * @param  string $path
     * @return int|float
     */
    public function getWidth($path)
    {
        return $this->imageDimensions->getWidth($path);
    }

    /**
     * Get image height
     *
     * @param  string $path
     * @return int|float
     */
    public function getHeight($path)
    {
        return $this->imageDimensions->getHeight($path);
    }
}

<?php
namespace Swissup\Easybanner\Helper;

class Image extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * File Uploader factory
     *
     * @var \Magento\Framework\Filesystem\Io\File
     */
    protected $_ioFile;
    /**
     * @var \Magento\Framework\Image\Factory
     */
    protected $_imageFactory;
    /**
     * image model
     *
     * @var \Swissup\Easybanner\Model\Data\Image
     */
    protected $_imageModel;
    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Filesystem\Io\File $ioFile
     * @param \Magento\Framework\Image\Factory $imageFactory
     * @param \Swissup\Easybanner\Model\Data\Image $imageModel
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Filesystem\Io\File $ioFile,
        \Magento\Framework\Image\Factory $imageFactory,
        \Swissup\Easybanner\Model\Data\Image $imageModel
    ) {
        $this->_ioFile = $ioFile;
        $this->_imageFactory = $imageFactory;
        $this->_imageModel = $imageModel;
        parent::__construct($context);
    }
    /**
     * Return URL for resized image
     *
     * @return bool|string
     */
    public function resize($banner, $width, $height)
    {
        $imageFile = $banner->getImage();
        $imageFile = ltrim($banner->getImage(), '/');

        $dir = '/' . 'cache' . '/' . $width . 'x' . $height . '/';
        $cacheDir = $this->getBaseDir() . $dir;
        $cacheUrl = $this->getBaseUrl() . $dir;

        $io = $this->_ioFile;
        $io->checkAndCreateFolder($cacheDir);
        $io->open(['path' => $cacheDir]);
        if ($io->fileExists($imageFile)) {
            return $cacheUrl . $imageFile;
        }

        try {
            $image = $this->_imageFactory->create($this->getBaseDir() . '/' . $imageFile);
            $image->keepAspectRatio(true);
            $image->keepFrame(true);
            $image->keepTransparency(true);
            $image->backgroundColor($this->getBackgroundColor($banner));
            $image->resize($width, $height);
            $image->save($cacheDir . '/' . $imageFile);
            return $cacheUrl . $imageFile;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getBackgroundColor($banner)
    {
        $rgb = $banner->getBackgroundColor();
        if (!$rgb) {
            return [255, 255, 255];
        }

        $rgb = explode(',', $rgb);
        if (count($rgb) !== 3) {
            return [255, 255, 255];
        }

        foreach ($rgb as $i => $color) {
            $rgb[$i] = (int) $color;
        }

        return $rgb;
    }

    /**
     * Return the base media directory testimonial images
     *
     * @return string
     */
    public function getBaseDir()
    {
        return $this->_imageModel->getBaseDir();
    }
    /**
     * Return the Base URL for News Item images
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->_imageModel->getBaseUrl();
    }
}
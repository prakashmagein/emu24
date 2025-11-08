<?php

namespace Swissup\EasySlide\Helper;

use Magento\Framework\UrlInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Filesystem\DirectoryList;

class Image extends AbstractHelper
{
    /**
     * Media sub directory
     *
     * @var string
     */
    protected $subDir = 'easyslide/';

    /**
     * Cache (resized) directory
     *
     * @var string
     */
    protected $cacheDir = 'resized/';

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    protected $ioFile;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $fileSystem;

    /**
     * @var \Magento\Framework\Image\Factory
     */
    protected $imageFactory;

    /**
     * @var \Swissup\Image\Helper\Dimensions
     */
    protected $imageDimensions;

    /**
     * @param \Magento\Framework\Filesystem\Io\File $ioFile
     * @param \Magento\Framework\Filesystem         $fileSystem
     * @param \Magento\Framework\Image\Factory      $imageFactory
     * @param \Swissup\Image\Helper\Dimensions      $imageDimensions
     * @param Context                               $context
     */
    public function __construct(
        \Magento\Framework\Filesystem\Io\File $ioFile,
        \Magento\Framework\Filesystem $fileSystem,
        \Magento\Framework\Image\Factory $imageFactory,
        \Swissup\Image\Helper\Dimensions $imageDimensions,
        Context $context
    ) {
        $this->ioFile = $ioFile;
        $this->fileSystem = $fileSystem;
        $this->imageFactory = $imageFactory;
        $this->imageDimensions = $imageDimensions;
        parent::__construct($context);
    }

    /**
     * Return URL for resized image
     *
     * @param $imageFile resize image url
     * @param $width resize image width
     * @param $height resize image height
     * @return bool|string
     */
    public function resize($imageFile, $width, $height = null)
    {
        if (!$imageFile) {
            return false;
        }

        $sizeDir = $this->cacheDir . $width . 'x' . $height . '/';
        $cachePath = $this->getBaseDir() . $sizeDir;
        $cacheUrl = $this->getBaseUrl() . $sizeDir;
        $this->ioFile->checkAndCreateFolder($cachePath);
        $this->ioFile->open(['path' => $cachePath]);
        if ($this->ioFile->fileExists($imageFile)) {
            return $cacheUrl . $imageFile;
        }

        try {
            $image = $this->imageFactory->create($this->getBaseDir() . $imageFile);
            $image->constrainOnly(true);
            $image->keepAspectRatio(true);
            $image->keepFrame(false);
            $image->keepTransparency(true);
            $image->resize($width, $height);
            $image->save($cachePath . '/' . $imageFile);
            return $cacheUrl . $imageFile;
        } catch (\Exception $e) {
            $imageType = exif_imagetype($this->getBaseDir() . $imageFile);
            $mimeType = image_type_to_mime_type($imageType);
            $this->_logger->error(
                __(
                    '%1 image resize. %2 . Mime type - %3',
                    $this->_getModuleName(),
                    $e->getMessage(),
                    $mimeType
                )
            );
        }

        return false;
    }

    /**
     * Get images base url of easyslide directory
     *
     * @return string
     */
    public function getBaseUrl($type = UrlInterface::URL_TYPE_MEDIA)
    {
        return $this->_urlBuilder
            ->getBaseUrl(['_type' => $type]) . $this->subDir;
    }

    /**
     * Get base image dir
     *
     * @return string
     */
    public function getBaseDir($directoryCode = DirectoryList::MEDIA)
    {
        return $this->fileSystem
            ->getDirectoryWrite($directoryCode)
            ->getAbsolutePath($this->subDir);
    }

    /**
     * Get width of image
     *
     * @param  string $imageFile
     * @return int|float
     */
    public function getImageWidth($imageFile)
    {
        $url = $this->getBaseUrl() . $imageFile;

        return $this->imageDimensions->getWidth($url);
    }

    /**
     * Get width of image
     *
     * @param  string $imageFile
     * @return int|float
     */
    public function getImageHeight($imageFile)
    {
        $url = $this->getBaseUrl() . $imageFile;
        return $this->imageDimensions->getHeight($url);
    }

    /**
     * Delete image file from $directory or easyslide baseDir
     *
     * @param  string $imageFile
     * @param  string $directory Absolute path
     * @return bool
     */
    public function delete($imageFile, $directory = null)
    {
        $filePath = ($directory ?: $this->getBaseDir()) . $imageFile;
        return $this->ioFile->rm($filePath);
    }

    /**
     * Clean resized cached images
     *
     * @param string $imageFile
     */
    public function cleanCached($imageFile)
    {
        $cachePath = $this->getBaseDir() . $this->cacheDir;
        foreach ($this->ioFile->getDirectoriesList($cachePath) as $directory) {
            $this->delete($imageFile, $directory . '/');
        }
    }

    /**
     * Copy file with new name
     *
     * @param  string $imageFile
     * @return string
     */
    public function duplicateImage($imageFile)
    {
        $parts = explode('.', $imageFile);
        $n = count($parts);
        if ($n - 2 >= 0) {
            $parts[$n - 2] .= '_' . uniqid();
        } else {
            $parts[0] .= '_' . uniqid();
        }

        $newImageFile = implode('.', $parts);
        $result = $this->ioFile->cp(
            $this->getBaseDir() . $imageFile,
            $this->getBaseDir() . $newImageFile
        );

        return $newImageFile;
    }

    public function getFileSize(string $imageFile): int
    {
        $mediaDirectory = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA);
        $path = $mediaDirectory->getAbsolutePath() . $this->subDir;
        $fileHandler = [];

        if (is_file($path . $imageFile)) {
            $fileHandler = $mediaDirectory->stat($this->subDir . $imageFile);
        }

        return (int)($fileHandler['size'] ?? 0);
    }
}

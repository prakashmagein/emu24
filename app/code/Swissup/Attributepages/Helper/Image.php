<?php
namespace Swissup\Attributepages\Helper;

use Magento\Framework\UrlInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Store\Model\ScopeInterface;
use Swissup\Attributepages\Model\Entity as AttributepagesModel;
use Swissup\Attributepages\Model\ImageData;
/**
 * Attributepages image helper
 */
class Image extends \Magento\Framework\App\Helper\AbstractHelper
{
    const CACHE_FOLDER = 'cache';
    /**
     * The page to take image from
     *
     * @var AttributepagesModel
     */
    protected $entity;
    /**
     * Image to resize
     *
     * @var string
     */
    protected $mode;
    /**
     * Background color to fill the resized image
     *
     * @var array(red, green, blue)
     */
    protected $backgroundColor = null;
    /**
     * Resize to width and height always
     *
     * @var boolean
     */
    protected $keepFrame = true;
    /**
     * File Uploader factory
     *
     * @var \Magento\Framework\Filesystem\Io\File
     */
    protected $ioFile;
    /**
     * image factory
     *
     * @var \Magento\Framework\Image\Factory
     */
    protected $imageFactory;
    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $fileSystem;
    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Filesystem\Io\File $ioFile
     * @param \Magento\Framework\Image\Factory $imageFactory
     * @param \Magento\Framework\Filesystem $fileSystem
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Filesystem\Io\File $ioFile,
        \Magento\Framework\Image\Factory $imageFactory,
        \Magento\Framework\Filesystem $fileSystem
    ) {
        $this->ioFile = $ioFile;
        $this->imageFactory = $imageFactory;
        $this->fileSystem = $fileSystem;
        parent::__construct($context);
    }
    /**
     * @param  AttributepagesModel $entity
     * @param  string $mode [image|thumbnail]
     * @return \Swissup\Attributepages\Helper\Image
     */
    public function init(AttributepagesModel $entity, $mode = 'image')
    {
        $this->entity = $entity;
        $this->mode   = $mode;
        return $this;
    }
    /**
     * Set background color to fill the resized image
     *
     * @param integer $r Red    [0-255]
     * @param integer $g Green  [0-255]
     * @param integer $b Blue   [0-255]
     */
    public function setBackgroundColor($r = 255, $g = 255, $b = 255)
    {
        $this->backgroundColor = [$r, $g, $b];
        return $this;
    }
    /**
     * set keep resize image frame
     * @param Boolean
     */
    public function setKeepFrame($flag)
    {
        $this->keepFrame = (bool)$flag;
        return $this;
    }
    /**
     * Return URL for resized image
     *
     * @param $width resize image width
     * @param $height resize image height
     * @return bool|string
     */
    public function resize($width, $height)
    {
        $image = $this->entity->getData($this->mode);
        if (empty($image)) {
            return '';
        }

        // svg images support
        if (strrpos($image, '.svg') === strlen($image) - 4) {
            return $this->getBaseUrl(ImageData::ENTITY_MEDIA_PATH . '/' . trim($image, '/'));
        }

        if (!$width || !is_numeric($width)) {
            $width = 200;
        }
        if (!$height || !is_numeric($height)) {
            $height = $width;
        }

        $folderPath = $this->getBaseDir(ImageData::ENTITY_MEDIA_PATH);
        $cacheDir   = $folderPath . '/' . self::CACHE_FOLDER;
        if (!file_exists($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }

        $pathinfo  = pathinfo($image);
        $imageName = implode('/', array_filter([
            $pathinfo['dirname'] === '.' ? false : $pathinfo['dirname'],
            $width . 'x' . $height . '_' . implode(',', $this->getBackgroundColor()),
            $pathinfo['basename']
        ]));

        $resizedImage  = $cacheDir . '/' . trim($imageName, '/');
        $originalImage = $folderPath . '/' . trim($image, '/');

        if (!file_exists($resizedImage) && file_exists($originalImage)) {
            try {
                $image = $this->imageFactory->create($originalImage);
                $image->constrainOnly(true);
                $image->keepAspectRatio(true);
                $image->keepFrame($this->keepFrame);
                $image->backgroundColor($this->getBackgroundColor());
                $image->resize($width, $height);
                $image->save($resizedImage);
            } catch (\Exception $e) {
                return $this->getBaseUrl(ImageData::ENTITY_MEDIA_PATH . '/' . trim($image, '/'));
            }
        }

        return $this->getBaseUrl(
            ImageData::ENTITY_MEDIA_PATH . '/' . self::CACHE_FOLDER . '/' . trim($imageName, '/')
        );
    }
    /**
     * Retrieve background color to fill the resized image
     *
     * @return array(red, green, blue)
     */
    public function getBackgroundColor()
    {
        if (null === $this->backgroundColor) {
            $key = 'attributepages/image/background';
            $rgb = $this->scopeConfig->getValue($key, ScopeInterface::SCOPE_STORE);
            $rgb = explode(',', $rgb);
            foreach ($rgb as $i => $color) {
                $rgb[$i] = (int) $color;
            }
            $this->backgroundColor = $rgb;
        }
        return $this->backgroundColor;
    }
    /**
     * get images base url
     *
     * @return string
     */
    public function getBaseUrl($path, $type = UrlInterface::URL_TYPE_MEDIA)
    {
        return $this->_urlBuilder
            ->getBaseUrl(['_type' => $type]) . $path;
    }
    /**
     * get base image dir
     *
     * @return string
     */
    public function getBaseDir($path, $directoryCode = DirectoryList::MEDIA)
    {
        return $this->fileSystem
            ->getDirectoryWrite($directoryCode)
            ->getAbsolutePath($path);
    }
}

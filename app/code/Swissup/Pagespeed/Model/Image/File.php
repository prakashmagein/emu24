<?php
namespace Swissup\Pagespeed\Model\Image;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\Product\Media\Config as MediaConfig;
use Magento\Framework\Exception\FileSystemException;

class File
{

    /**
     * @var \Swissup\Pagespeed\Helper\Config
     */
    protected $config;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     *
     * @var \Magento\Catalog\Model\Product\Media\Config
     */
    protected $mediaConfig;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var array
     */
    private $readInstances = [];

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    protected $ioFile;

    /**
     *
     * @var string
     */
    protected $mediaUrlPath;

    /**
     * @var string
     */
    protected $staticBaseUrlPath;

    /**
     * @param \Swissup\Pagespeed\Helper\Config $config
     * @param MediaConfig $mediaConfig
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Filesystem\Io\File $ioFile
     */
    public function __construct(
        \Swissup\Pagespeed\Helper\Config $config,
        MediaConfig $mediaConfig,
        StoreManagerInterface $storeManager,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Filesystem\Io\File $ioFile
    ) {
        $this->config = $config;
        $this->mediaConfig = $mediaConfig;
        $this->storeManager = $storeManager;
        $this->filesystem = $filesystem;
        $this->ioFile = $ioFile;
    }

        /**
     * @param string $directoryCode
     * @return \Magento\Framework\Filesystem\Directory\WriteInterface|mixed
     * @throws FileSystemException
     */
    private function getDirectoryRead($directoryCode)
    {
        if (!isset($this->readInstances[$directoryCode])) {
            $this->readInstances[$directoryCode] = $this->filesystem->getDirectoryRead($directoryCode);
        }

        return $this->readInstances[$directoryCode];
    }

    /**
     * insteadof parse_url($url, )
     *
     * @param $url
     * @return mixed
     */
    private function getUrlPath(string $url): ?string
    {
        try {
            $uri = \Laminas\Uri\UriFactory::factory($url);
            $path = $uri->getPath();

            if (strpos($path, '//') !== false) {
                $path = preg_replace('/(\/+)/', '/', $path);
            }

            return $path;
        } catch (\Laminas\Uri\Exception\InvalidArgumentException $e) {
            return null;
        }
    }

    /**
     *
     * @return string
     */
    private function getMediaProductUrlPath()
    {
        if ($this->mediaUrlPath === null) {
            $baseMediaUrl = $this->mediaConfig->getBaseMediaUrl();
            $this->mediaUrlPath = (string) $this->getUrlPath($baseMediaUrl);
        }

        return $this->mediaUrlPath;
    }

    /**
     *
     * @return string
     */
    private function getStaticBaseUrlPath()
    {
        if ($this->staticBaseUrlPath === null) {
            $store = $this->storeManager->getStore();
            $staticBaseUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_STATIC);
            $this->staticBaseUrlPath = $this->getUrlPath($staticBaseUrl);
        }

        return $this->staticBaseUrlPath;
    }

    /**
     *
     * @param  string  $imageUrl
     * @return boolean
     */
    public function isMediaProductUrl($imageUrl)
    {
        $mediaUrlPath = (string) $this->getMediaProductUrlPath();
        $imagePath = (string) $this->getUrlPath($imageUrl);
        if ($imagePath === null) {
            return false;
        }
        return strpos($imagePath, $mediaUrlPath) === 0;
    }

    /**
     *
     * @param  string  $imageUrl
     * @return boolean
     */
    public function isMediaCustomUrl($imageUrl)
    {
        $imagePath = (string) $this->getUrlPath($imageUrl);
        if ($imagePath === null) {
            return false;
        }
        // return strpos($imagePath, '/media/wysiwyg/') === 0;

        $targetDirs = $this->config->getResizeCommandTargetDirs();
        foreach ($targetDirs as $targetDir) {
            if (strpos($imagePath, '/' . DirectoryList::MEDIA . "/{$targetDir}/") !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $imageUrl
     * @return false
     */
    public function isPubStaticUrl($imageUrl)
    {
        $imagePath = (string) $this->getUrlPath($imageUrl);
        if ($imagePath === null) {
            return false;
        }
        $staticPathPrefix = (string) $this->getStaticBaseUrlPath();

        return !empty($staticPathPrefix) && strpos($imagePath, $staticPathPrefix) === 0;
    }

    /**
     *
     * @param  string  $imageUrl
     * @return boolean
     */
    public function isMediaImageFileExist($imageUrl)
    {
        $relativePath = false;
        $mediaUrlPath = (string) $this->getMediaProductUrlPath();
        $imagePath = (string) $this->getUrlPath($imageUrl);

        if (strpos($imagePath, $mediaUrlPath) === 0) {
            $relativeFilename = substr_replace($imagePath, '', 0, strlen($mediaUrlPath));
            $relativePath = $this->mediaConfig->getMediaPath($relativeFilename);
        }

        $targetDirs = $this->config->getResizeCommandTargetDirs();
        $targetDirs[] = '.';
        $mediaPrefix = DirectoryList::MEDIA;

        foreach ($targetDirs as $targetDir) {
            $targetDirCheckpoint = "/{$mediaPrefix}/{$targetDir}/";
            if ($targetDir === '.') {
                $targetDirCheckpoint = "/{$mediaPrefix}/";
            }
            if (strpos($imagePath, $targetDirCheckpoint) !== false) {
                // $relativeFilename = false;
                list(, $relativePath) = explode("/{$mediaPrefix}/", $imagePath);
                break;
            }
        }

        return $relativePath && $this->getDirectoryRead(DirectoryList::MEDIA)->isFile($relativePath);
    }

    /**
     *
     * @param  string  $imageUrl
     * @return boolean
     */
    public function isPubStaticImageFileExist($imageUrl)
    {
        $relativePath = false;
        $staticUrlPath = (string) $this->getStaticBaseUrlPath();
        $imagePath = (string) $this->getUrlPath($imageUrl);

        if (strpos($imagePath, $staticUrlPath) === 0) {
            $relativeFilename = substr_replace($imagePath, '', 0, strlen($staticUrlPath));
            $relativeFilename = $relativeFilename === null ?
                '' : ltrim(str_replace('\\', '/', $relativeFilename), '/');
            $relativePath = $relativeFilename;//$this->getStaticPath($relativeFilename);
        }

        return $relativePath && $this->getDirectoryRead(DirectoryList::STATIC_VIEW)->isFile($relativePath);
    }

    /**
     * @param string $filename
     * @return mixed
     */
    public function getFileBasename($filename)
    {
        $pathInfo = $this->ioFile->getPathInfo($filename);
        $part = 'basename';
        return isset($pathInfo[$part]) ? $pathInfo[$part] : false;
    }

    /**
     * @param string $filename
     * @return mixed
     */
    public function getFileExtension($filename)
    {
        $pathInfo = $this->ioFile->getPathInfo($filename);
        $part = 'extension';
        return isset($pathInfo[$part]) ? $pathInfo[$part] : false;
    }

    /**
     * @param string $filename
     * @return mixed
     */
    public function getFilename($filename)
    {
        $pathInfo = $this->ioFile->getPathInfo($filename);
        return $pathInfo['filename'];
    }

    /**
     * @param string $url
     * @return string
     */
    public function getFileExtensionFromUrl(string $url): string
    {
        $path = $this->getUrlPath($url);
        if (!$path) {
            return '';
        }

        $pathInfo = $this->ioFile->getPathInfo($path);
        $extension = $pathInfo['extension'] ?? '';

        list($extension) = explode('?', $extension, 2);
        return strtolower($extension);
    }
}

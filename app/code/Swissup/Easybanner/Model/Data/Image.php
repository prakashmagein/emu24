<?php

namespace Swissup\Easybanner\Model\Data;

use Magento\Framework\UrlInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\File\Mime;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class Image
{
    /**
     * Path in /pub/media directory
     */
    const ENTITY_MEDIA_PATH = 'easybanner';

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * @var Mime
     */
    protected $mime;

    /**
     * @var WriteInterface
     */
    protected $mediaDirectory;

    /**
     * @param UrlInterface $urlBuilder
     * @param Filesystem $fileSystem
     * @param Mime $mime
     */
    public function __construct(
        UrlInterface $urlBuilder,
        Filesystem $fileSystem,
        Mime $mime
    )
    {
        $this->urlBuilder = $urlBuilder;
        $this->fileSystem = $fileSystem;
        $this->mime = $mime;
    }

    /**
     * get images base url
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->urlBuilder->getBaseUrl([
            '_type' => UrlInterface::URL_TYPE_MEDIA
        ]) . self::ENTITY_MEDIA_PATH;
    }

    /**
     * get base image dir
     *
     * @return string
     */
    public function getBaseDir()
    {
        return $this->fileSystem->getDirectoryWrite(DirectoryList::MEDIA)
            ->getAbsolutePath(self::ENTITY_MEDIA_PATH);
    }

    /**
     * Get WriteInterface instance
     *
     * @return WriteInterface
     */
    private function getMediaDirectory()
    {
        if ($this->mediaDirectory === null) {
            $this->mediaDirectory = $this->fileSystem->getDirectoryWrite(DirectoryList::MEDIA);
        }
        return $this->mediaDirectory;
    }

    /**
     * Retrieve MIME type of requested file
     *
     * @param string $fileName
     * @return string
     */
    public function getMimeType($fileName)
    {
        $filePath = self::ENTITY_MEDIA_PATH . '/' . ltrim($fileName, '/');
        $absoluteFilePath = $this->getMediaDirectory()->getAbsolutePath($filePath);

        $result = $this->mime->getMimeType($absoluteFilePath);
        return $result;
    }

    /**
     * Get file statistics data
     *
     * @param string $fileName
     * @return array
     */
    public function getStat($fileName)
    {
        $filePath = self::ENTITY_MEDIA_PATH . '/' . ltrim($fileName, '/');

        $result = $this->getMediaDirectory()->stat($filePath);
        return $result;
    }
}

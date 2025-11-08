<?php

namespace Swissup\Attributepages\Model;

use Magento\Framework\UrlInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\File\Mime;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class ImageData
{
    const ENTITY_MEDIA_PATH = 'swissup/attributepages';

    protected UrlInterface $urlBuilder;

    protected Filesystem $fileSystem;

    protected Mime $mime;

    protected $mediaDirectory;

    public function __construct(
        UrlInterface $urlBuilder,
        Filesystem $fileSystem,
        Mime $mime
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->fileSystem = $fileSystem;
        $this->mime = $mime;
    }

    public function getBaseUrl(): string
    {
        return $this->urlBuilder->getBaseUrl([
            '_type' => UrlInterface::URL_TYPE_MEDIA
        ]) . self::ENTITY_MEDIA_PATH;
    }

    public function getBaseDir(): string
    {
        return $this->fileSystem->getDirectoryWrite(DirectoryList::MEDIA)
            ->getAbsolutePath(self::ENTITY_MEDIA_PATH);
    }

    private function getMediaDirectory(): WriteInterface
    {
        if ($this->mediaDirectory === null) {
            $this->mediaDirectory = $this->fileSystem->getDirectoryWrite(DirectoryList::MEDIA);
        }
        return $this->mediaDirectory;
    }

    public function getMimeType($fileName): string
    {
        $filePath = self::ENTITY_MEDIA_PATH . '/' . ltrim($fileName, '/');
        $absoluteFilePath = $this->getMediaDirectory()->getAbsolutePath($filePath);
        $result = $this->mime->getMimeType($absoluteFilePath);
        return $result;
    }

    public function getStat($fileName): array
    {
        $filePath = self::ENTITY_MEDIA_PATH . '/' . ltrim($fileName, '/');
        $result = $this->getMediaDirectory()->stat($filePath);
        return $result;
    }
}

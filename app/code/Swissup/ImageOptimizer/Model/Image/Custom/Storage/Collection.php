<?php

namespace Swissup\ImageOptimizer\Model\Image\Custom\Storage;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;

/**
 * Images storage collection
 *
 */
class Collection extends \Magento\Framework\Data\Collection\Filesystem
{
    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var array
     */
    private $writeInstances = [];

    /**
     * @var string|null
     */
    private $staticAbsolutePath;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Magento\Framework\Filesystem $filesystem
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Magento\Framework\Filesystem $filesystem
    ) {
        parent::__construct($entityFactory);
        $this->filesystem = $filesystem;
    }

    /**
     * @param string $directoryCode
     * @return \Magento\Framework\Filesystem\Directory\WriteInterface|mixed
     * @throws FileSystemException
     */
    private function getWrite($directoryCode)
    {
        if (!isset($this->writeInstances[$directoryCode])) {
            $this->writeInstances[$directoryCode] = $this->filesystem->getDirectoryWrite($directoryCode);
        }

        return $this->writeInstances[$directoryCode];
    }

    /**
     * @return \Magento\Framework\Filesystem\Directory\WriteInterface|mixed
     * @throws FileSystemException
     */
    private function getMediaDirWriter()
    {
        return $this->getWrite(DirectoryList::MEDIA);
    }

    /**
     * @return \Magento\Framework\Filesystem\Directory\WriteInterface
     * @throws FileSystemException
     */
    private function getStaticDirWriter()
    {
        return $this->getWrite(DirectoryList::STATIC_VIEW);
    }

    /**
     * @return string|null
     * @throws FileSystemException
     */
    private function getStaticAbsolutePath()
    {
        if ($this->staticAbsolutePath === null) {
            $this->staticAbsolutePath = $this->getStaticDirWriter()->getAbsolutePath();
        }
        return $this->staticAbsolutePath;
    }

    /**
     * @param $filename
     * @return bool
     */
    private function isStaticFile($filename)
    {
        return str_starts_with($filename, $this->getStaticAbsolutePath());
    }

    /**
     * Generate row
     *
     * @param string $filename
     * @return array
     */
    protected function _generateRow($filename)
    {
        $filename = $filename !== null ?
            preg_replace('~[/\\\]+(?<![htps?]://)~', '/', $filename) : '';

        $path = $this->isStaticFile($filename) ? $this->getStaticDirWriter() : $this->getMediaDirWriter();
        try {
            $mtime = $path->stat($path->getRelativePath($filename))['mtime'];
        } catch (FileSystemException $e) {
            $mtime = 0;
        }
        return [
            'filename' => rtrim($filename, '/'),
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            'basename' => basename($filename),
            'mtime' => $mtime
        ];
    }

    /**
     * @param $plusSize
     * @return $this
     */
    public function inreaseSize($plusSize)
    {
        $this->_totalRecords += $plusSize;
        return $this;
    }
}

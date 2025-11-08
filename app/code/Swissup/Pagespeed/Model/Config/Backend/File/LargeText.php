<?php
namespace Swissup\Pagespeed\Model\Config\Backend\File;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

class LargeText extends \Magento\Framework\App\Config\Value
{
    /**
     * pub/media/critical-css
     *
     * @var string
     */
    protected $dir = 'critical-css';

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $mediaDirectoryWriter;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param Filesystem $filesystem
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        Filesystem $filesystem,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->filesystem = $filesystem;
        $this->mediaDirectoryWriter = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);

        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Save text to file before saving filename to config value
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSave()
    {
        $contents = $this->getValue();

        $filename = $this->getRelativeFilePath('critical.css');
        $file = $this->mediaDirectoryWriter->openFile($filename, 'w');
        try {
            $file->lock();
            try {
                $file->write($contents);

                $value = $this->mediaDirectoryWriter->getAbsolutePath($filename);
                $this->setValue($value);
            } finally {
                $file->unlock();
            }
        } finally {
            $file->close();
        }

        return $this;
    }

    /**
     *
     * @param  string $filename
     * @return string
     */
    protected function getRelativeFilePath($filename)
    {
        $filename = $this->appendScopeInfo($filename);
        $filename = '/' . $this->dir . '/' . $filename;

        return $filename;
    }

    /**
     * Add scope info to path
     *
     * E.g. 'stores/2/path' , 'websites/3/path', 'default/path'
     *
     * @param string $path
     * @return string
     */
    protected function appendScopeInfo($path)
    {
        if (ScopeConfigInterface::SCOPE_TYPE_DEFAULT != $this->getScope()) {
            $path = $this->getScopeId() . '/' . $path;
        }
        $path = $this->getScope() . '/' . $path;
        return $path;
    }

    /**
     *
     * @inherit
     */
    protected function _afterLoad()
    {
        $filename = (string) $this->getValue();
        // $filename = $this->getRelativeFilePath('critical.css');
        $writer = $this->mediaDirectoryWriter;

        $this->setValue(''); // do not show filename in the field

        if ($writer->isExist($filename) &&
            $writer->isFile($filename) &&
            $writer->isReadable($filename)
        ) {
            $value = (string) $writer->readFile($filename);
            $isFilePath = strpos($value, '/critical.css') !== false && strpos($value, '{') === false;
            if (!empty($value) && !$isFilePath) {
                $this->setValue($value);
            }
        }
        return $this;
    }
}

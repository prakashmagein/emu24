<?php
namespace Swissup\Pagespeed\Model\Config\Backend\File;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Module\Dir;

class Rjs extends \Magento\Framework\App\Config\Value
{
    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    private $moduleReader;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\Module\Dir\Reader $moduleReader
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Module\Dir\Reader $moduleReader,
        \Magento\Framework\Filesystem $filesystem,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);

        $this->moduleReader = $moduleReader;
        $this->filesystem = $filesystem;
    }

    /**
     *
     * @inherit
     */
    protected function _afterLoad()
    {
        $value = (string) $this->getValue();
        if (empty($value)) {
            $filename = 'rjs.json';
            // try to read app/etc/rjs.json
            $reader = $this->filesystem->getDirectoryRead(DirectoryList::CONFIG);
            $value = $this->readFile($reader, $filename);
            if (!empty($value)) {
                $this->setValue($value);
            }
        }
        if (empty($value)) {
            $filename = 'rjs.json';
            $moduleEtcDir = $this->moduleReader->getModuleDir(
                Dir::MODULE_ETC_DIR,
                'Swissup_Pagespeed'
            );
            // try to read vendor/swissup/module-pagespeed/etc/rjs.json
            $reader = $this->filesystem->getDirectoryReadByPath($moduleEtcDir);
            $value = $this->readFile($reader, $filename);
            if (!empty($value)) {
                $this->setValue($value);
            }
        }

        return $this;
    }

    private function readFile(\Magento\Framework\Filesystem\Directory\ReadInterface $reader, $filename = 'rjs.json')
    {
        return $reader->isExist($filename) && $reader->isFile($filename) && $reader->isReadable($filename) ?
            $reader->readFile($filename) : '';
    }
}

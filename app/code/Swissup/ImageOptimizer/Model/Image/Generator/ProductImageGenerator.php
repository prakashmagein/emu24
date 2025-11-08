<?php
declare(strict_types=1);

namespace Swissup\ImageOptimizer\Model\Image\Generator;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Query\Generator;
use Magento\Framework\DB\Select;
use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Model\ResourceModel\Product\Gallery;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Catalog\Model\Product\Media\ConfigInterface as ProductMediaConfig;

class ProductImageGenerator extends \Swissup\ImageOptimizer\Model\Image\Generator\AbstractGenerator
{
    /**
     * @var Generator
     */
    private $batchQueryGenerator;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $mediaDirectory;

    /**
     * @var ProductMediaConfig
     */
    private $productMediaConfig;

    /**
     * @var int
     */
    private $batchSize;

    /**
     * @param \Magento\Framework\App\CacheInterface $cache
     * @param \Magento\Framework\App\Cache\StateInterface $cacheState
     * @param Generator $generator
     * @param ResourceConnection $resourceConnection
     * @param \Magento\Framework\Filesystem $filesystem
     * @param ProductMediaConfig $productMediaConfig
     * @param int $batchSize
     */
    public function __construct(
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\App\Cache\StateInterface $cacheState,
        Generator $generator,
        ResourceConnection $resourceConnection,
        \Magento\Framework\Filesystem $filesystem,
        ProductMediaConfig $productMediaConfig,
        $batchSize = 100
    ) {
        parent::__construct($cache, $cacheState);
        $this->batchQueryGenerator = $generator;
        $this->resourceConnection = $resourceConnection;
        $this->connection = $this->resourceConnection->getConnection();
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->productMediaConfig = $productMediaConfig;
        $this->batchSize = $batchSize;
    }

    /**
     * Returns product images
     *
     * @return \Generator
     */
    public function create(): \Generator
    {
        $batchSelectIterator = $this->batchQueryGenerator->generate(
            'value_id',
            $this->getVisibleImagesSelect(),
            $this->batchSize,
            \Magento\Framework\DB\Query\BatchIteratorInterface::NON_UNIQUE_FIELD_ITERATOR
        );

        $i = 0;
        $page = $this->loadCurPage() ?: 0;
        $pageSize = $this->pageSize;
        $hasResults = false;
        foreach ($batchSelectIterator as $select) {
            foreach ($this->connection->fetchAll($select) as $key => $row) {
                $row['path'] = $this->getAbsolutePath($row['filepath']);
                $row['filename'] = $row['filepath'];
                if (!$this->isFileExist($row['path'])) {
                    continue;
                }
                $i++;
                if ($i <= $page * $pageSize) {
                    continue;
                }
                if ($i > ($page + 1) * $pageSize) {
                    break 2;
                }
                $hasResults = true;
                yield $key => $row;
            }
        }
        $page++;
        if (!$hasResults) {
            $page = null;
        }
        $this->saveCurPage($page);
    }

    /**
     * Get the number of unique pictures of products
     * @return int
     */
    public function getCountAllProductImages(): int
    {
        $select = $this->getVisibleImagesSelect()
            ->reset('columns')
            ->columns('count(*)');

        return (int) $this->connection->fetchOne($select);
    }

    /**
     * @return Select
     */
    private function getVisibleImagesSelect(): Select
    {
        $select = $this->connection->select()
            ->distinct()
            ->from(
                ['images' => $this->resourceConnection->getTableName(Gallery::GALLERY_TABLE)],
                'value as filepath'
            )->where(
                'disabled = 0'
            );

        $filename = $this->filenameFilter;
        if (!empty($filename)) {
            $select->where('images.value LIKE ?', '%' . $filename . '%');
        }

        return $select;
    }

    /**
     * @param string $filename
     * @return string
     */
    private function getAbsolutePath($filename)
    {
        $fileMediaPath = $this->productMediaConfig->getMediaPath($filename);
        return $this->mediaDirectory->getAbsolutePath($fileMediaPath);
    }

    /**
     * @param string $path
     * @return bool
     */
    private function isFileExist($path)
    {
        return $this->mediaDirectory->isExist($path) && $this->mediaDirectory->isFile($path);
    }
}

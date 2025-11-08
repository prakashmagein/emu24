<?php

namespace Swissup\SoldTogetherImportExport\Model\Import;

use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\ImportExport\Helper\Data as ImportHelper;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\ResourceModel\Helper;
use Magento\ImportExport\Model\ResourceModel\Import\Data;
use Swissup\SoldTogetherImportExport\Model\ProductIdStorage;

class Context
{
    /**
     * @var Data
     */
    private $importData;

    /**
     * @var Helper
     */
    private $resourceHelper;

    /**
     * @var ImportHelper
     */
    private $importExportData;

    /**
     * @var JsonHelper
     */
    private $jsonHelper;

    /**
     * @var ProcessingErrorAggregatorInterface
     */
    private $errorAggregator;

    /**
     * @var ProductIdStorage
     */
    private $productIdStorage;

    /**
     * @param Data                               $importData
     * @param Helper                             $resourceHelper
     * @param ImportHelper                       $importExportData
     * @param JsonHelper                         $jsonHelper
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @param ProductIdStorage                   $productIdStorage
     */
    public function __construct(
        Data $importData,
        Helper $resourceHelper,
        ImportHelper $importExportData,
        JsonHelper $jsonHelper,
        ProcessingErrorAggregatorInterface $errorAggregator,
        ProductIdStorage $productIdStorage
    ) {
        $this->importData = $importData;
        $this->resourceHelper = $resourceHelper;
        $this->importExportData = $importExportData;
        $this->jsonHelper = $jsonHelper;
        $this->errorAggregator = $errorAggregator;
        $this->productIdStorage = $productIdStorage;
    }

    /**
     * @return Data
     */
    public function getImportData()
    {
        return $this->importData;
    }

    /**
     * @return Helper
     */
    public function getResourceHelper()
    {
        return $this->resourceHelper;
    }

    /**
     * @return ImportHelper
     */
    public function getImportExportData()
    {
        return $this->importExportData;
    }

    /**
     * @return JsonHelper
     */
    public function getJsonHelper()
    {
        return $this->jsonHelper;
    }

    /**
     * @return ProcessingErrorAggregatorInterface
     */
    public function getErrorAggregator()
    {
        return $this->errorAggregator;
    }

    /**
     * @return ProductIdStorage
     */
    public function getProductIdStorage()
    {
        return $this->productIdStorage;
    }
}

<?php

namespace Swissup\SoldTogetherImportExport\Model\Import;

use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\Entity\AbstractEntity;
use Swissup\SoldTogetherImportExport\Model\LinkDataMerger\AbstractMerger;
use Swissup\SoldTogetherImportExport\Model\LinkDataStorage\AbstractStorage;

abstract class AbstractLink extends AbstractEntity
{
    /**
     * @var AbstractStorage
     */
    protected $linkDataStorage;

    /**
     * @var AbstractMerger
     */
    protected $linkDataMerger;

    /**
     * @var \Swissup\SoldTogetherImportExport\Model\ProductIdStorage
     */
    protected $productIdStorage;

    /**
     * @param AbstractMerger  $linkDataMerger
     * @param AbstractStorage $linkDataStorage
     * @param Context         $context
     */
    public function __construct(
        AbstractMerger $linkDataMerger,
        AbstractStorage $linkDataStorage,
        Context $context
    ) {
        $this->linkDataStorage = $linkDataStorage;
        $this->linkDataMerger = $linkDataMerger;

        $this->_dataSourceModel = $context->getImportData();
        $this->_importExportData = $context->getImportExportData();
        $this->_resourceHelper = $context->getResourceHelper();
        $this->errorAggregator = $context->getErrorAggregator();
        $this->jsonHelper = $context->getJsonHelper();
        $this->productIdStorage = $context->getProductIdStorage();

        $this->initMessageTemplates();
    }

    /**
     * Init Error Messages
     */
    protected function initMessageTemplates()
    {
        $this->addMessageTemplate(
            'SkuIsRequired',
            __('SKU cannot be empty.')
        );
        $this->addMessageTemplate(
            'RelatedSkuIsRequired',
            __('Related SKU cannot be empty.')
        );
        $this->addMessageTemplate(
            'SkuIsInvalid',
            __('Product for SKU not found.')
        );
        $this->addMessageTemplate(
            'RelatedSkuIsInvalid',
            __('Product for related SKU not found.')
        );
        $this->addMessageTemplate(
            'WeightIsInvalid',
            __('Relation weight should be greater than 0.')
        );
    }

    /**
     * Row validation
     *
     * @param array $rowData
     * @param int   $rowNum
     * @param bool  $isValidateProductId
     *
     * @return bool
     */
    public function validateRow(
        array $rowData,
        $rowNum,
        $isValidateProductId = false
    ): bool {
        $sku = $rowData['product_sku'] ?? '';
        $relatedSku = $rowData['related_sku'] ?? '';
        $weight = (int)($rowData['weight'] ?? '');

        if (!$sku) {
            $this->addRowError('SkuIsRequired', $rowNum);
        }

        if (!$relatedSku) {
            $this->addRowError('RelatedSkuIsRequired', $rowNum);
        }

        if (!$weight) {
            $this->addRowError('WeightIsInvalid', $rowNum);
        }

        if ($isValidateProductId) {
            $productId = $rowData['product_id'] ?? '';
            $relatedId = $rowData['related_id'] ?? '';

            if (!$productId) {
                $this->addRowError('SkuIsInvalid', $rowNum);
            }

            if (!$relatedId) {
                $this->addRowError('RelatedSkuIsInvalid', $rowNum);
            }
        }

        if (isset($this->_validatedRows[$rowNum])) {
            return !$this->getErrorAggregator()->isRowInvalid($rowNum);
        }

        $this->_validatedRows[$rowNum] = true;

        return !$this->getErrorAggregator()->isRowInvalid($rowNum);
    }

    /**
     * Import data
     *
     * @return bool
     *
     * @throws Exception
     */
    protected function _importData(): bool
    {
        $behavior = $this->getBehavior();
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $importRows = [];

            $this->productIdStorage->load($bunch);
            // Set product IDs for rows using SKUs
            foreach ($bunch as &$row) {
                $id = $this->productIdStorage->getId($row['product_sku']);
                $relatedId = $this->productIdStorage->getId($row['related_sku']);
                $row['product_id'] = $id;
                $row['related_id'] = $relatedId;
            }
            unset($row);

            $this->linkDataStorage->load($bunch);

            $isValidateProductId = true;
            foreach ($bunch as $rowNum => $row) {
                $isValid = $this->validateRow($row, $rowNum, $isValidateProductId);
                if (!$isValid) {
                    continue;
                }

                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);

                    continue;
                }

                $dbRow = $this->linkDataStorage->getData($row['product_id'], $row['related_id']);
                $rowKey = $row['product_id'] . ':' . $row['related_id'];

                if (Import::BEHAVIOR_DELETE === $behavior) {
                    // delete data
                    if (!empty($dbRow)) {
                        $importRows[$rowKey] = $dbRow['relation_id'];

                        $this->countItemsDeleted++;
                    }
                } else {
                    // update/add or replace data
                    $newRow = $this->linkDataMerger->merge($dbRow, $row);
                    $importRows[$rowKey] = $this->linkDataStorage->serializeFields($newRow);

                    $this->countItemsCreated += (int) empty($dbRow);
                    $this->countItemsUpdated += (int) !empty($dbRow);
                }
            }

            if (empty($importRows)) {
                continue;
            }

            if (Import::BEHAVIOR_DELETE === $behavior) {
                $this->linkDataStorage->deleteFromDb(['relation_id IN (?)' => $importRows]);
            } else {
                if (Import::BEHAVIOR_APPEND === $behavior) {
                    // get fields conditiona when import behavior is "Add/Update"
                    $fields = $this->getOnAppendConditions();
                } else {
                    // Otherwise we assume import behavior is "Replace"
                    $fields = $this->getOnReplaceConditions();
                }

                $this->linkDataStorage->insertOnDuplicateIntoDb($importRows, $fields);
            }
        }

        return true;
    }

    /**
     * Prepare ON DUPLICATE KEY conditions once behavior is add/update
     *
     * @return array
     */
    abstract protected function getOnAppendConditions(): array;

    /**
     * Prepare ON DUPLICATE KEY conditions once behavior is replace
     *
     * @return array
     */
    abstract protected function getOnReplaceConditions(): array;
}

<?php

namespace Swissup\SoldTogetherImportExport\Model\Export;

use Swissup\SoldTogether\Model\ResourceModel\AbstractResourceModel;
use Swissup\SoldTogetherImportExport\Model\Export\Link\AbstractCollectionFactory;

abstract class AbstractLink extends \Magento\ImportExport\Model\Export\AbstractEntity
{
    /**
     * @var HeaderProvider
     */
    protected $headerProvider;

    /**
     * @var Link\AttributeCollectionProvider
     */
    protected $attributeCollectionProvider;

    /**
     * @var AbstractCollectionFactory
     */
    protected $entityCollectionFactory;

    /**
     * @var AbstractResourceModel
     */
    protected $resource;

    /**
     * @param AbstractCollectionFactory $entityCollectionFactory
     * @param AbstractResourceModel     $resource
     * @param Context                   $context
     * @param array                     $data
     */
    public function __construct(
        AbstractCollectionFactory $entityCollectionFactory,
        AbstractResourceModel $resource,
        Context $context,
        array $data = []
    ) {
        $this->entityCollectionFactory = $entityCollectionFactory;
        $this->resource = $resource;

        $this->attributeCollectionProvider = $context->getAttributeCollectionProvider();
        $this->headerProvider = $context->getHeaderProvider();

        parent::__construct(
            $context->getScopeConfig(),
            $context->getStoreManager(),
            $context->getCollectionFactory(),
            $context->getResourceColFactory(),
            $data
        );

        if (empty($this->_pageSize)) {
            $this->_pageSize = 1000;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function _getEntityCollection()
    {
        return $this->entityCollectionFactory->create(
            $this->getAttributeCollection(),
            $this->_parameters
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function _getHeaderColumns()
    {
        return $this->headerProvider->getHeaders(
            $this->getAttributeCollection(),
            $this->_parameters
        );
    }

    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public function getAttributeCollection()
    {
        return $this->attributeCollectionProvider->get();
    }

    /**
     * Export given order link data
     *
     * @param \Swissup\SoldTogether\Model\Order $item
     * @return void
     */
    public function exportItem($item)
    {
        $row = $item->getData();
        $this->resource->unserializeFields($item);
        $dataSerialized = $item->getDataSerialized();
        if (is_array($dataSerialized)) {
            $row += $dataSerialized;
        }

        $this->getWriter()->writeRow($row);
    }

    /**
     * Export process.
     *
     * @return string
     */
    public function export()
    {
        // create export file
        $writer = $this->getWriter();
        $writer->setHeaderCols($this->_getHeaderColumns());
        $this->_exportCollectionByPages($this->_getEntityCollection());

        return $writer->getContents();
    }
}

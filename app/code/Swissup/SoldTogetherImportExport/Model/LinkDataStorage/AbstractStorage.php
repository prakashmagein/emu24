<?php

namespace Swissup\SoldTogetherImportExport\Model\LinkDataStorage;

abstract class AbstractStorage
{
    /**
     * @var array
     */
    protected $linkData = [];

    /**
     * @var \Swissup\SoldTogether\Model\AbstractModel
     */
    protected $linkEntity;

    /**
     * @var \Swissup\SoldTogether\Model\ResourceModel\AbstractResourceModel
     */
    protected $resource;

    /**
     * Load data to storage
     *
     * @param  array $bunch
     * @return void
     */
    public function load(array $bunch)
    {
        $connection = $this->resource->getConnection();
        $select = $connection
            ->select()
            ->from(
                $this->resource->getMainTable()
            );

        $fetchData = false;
        foreach ($bunch as $row) {
            $productId = $row['product_id'] ?? '';
            $relatedId = $row['related_id'] ?? '';
            if ($productId && $relatedId) {
                $select->orWhere("product_id = {$productId} AND related_id = {$relatedId}");
                $fetchData = true;
            }
        }

        $rawData = $fetchData ? $connection->fetchAll($select) : [];
        $this->linkData = [];
        foreach ($rawData as $item) {
            $key = $this->buildDataKey($item);
            $this->linkData[$key] = $this->unserializeFields($item);
        }
    }

    /**
     * Get data stored data
     *
     * @param  string|int $productId
     * @param  string|int $relatedId
     * @return array
     */
    public function getData($productId, $relatedId): array
    {
        $key = $this->buildDataKey([
            'product_id' => $productId,
            'related_id' => $relatedId
        ]);

        return $this->linkData[$key] ?? [];
    }

    /**
     * @param  array  $row
     * @return string
     */
    protected function buildDataKey(array $row): string
    {
        $productId = $row['product_id'] ?? '';
        $relatedId = $row['related_id'] ?? '';

        return "{$productId}:{$relatedId}";
    }

    /**
     * @param  array  $rawItem
     * @return array
     */
    public function unserializeFields(array $rawItem): array
    {
        $this->linkEntity->setData($rawItem);
        $this->resource->unserializeFields($this->linkEntity);

        return $this->linkEntity->getData() ?: [];
    }

    /**
     * @param  array $item
     * @return array
     */
    public function serializeFields(array $item): array
    {
        $this->linkEntity->setData($item);
        $this->resource->serializeFields($this->linkEntity);

        return $this->linkEntity->getData() ?: [];
    }

    /**
     * @param  mixed $where DELETE WHERE clause(s).
     * @return int          The number of affected rows.
     */
    public function deleteFromDb($where)
    {
        $connection = $this->resource->getConnection();
        $table = $this->resource->getMainTable();

        return $connection->delete($table, $where);
    }

    /**
     * @param  array $data   Column-value pairs or array of column-value pairs
     * @param  array $fields Update fields pairs or values
     * @return int           The number of affected rows.
     */
    public function insertOnDuplicateIntoDb(array $data, array $fields)
    {
        $connection = $this->resource->getConnection();
        $table = $this->resource->getMainTable();

        return $connection->insertOnDuplicate($table, $data, $fields);
    }
}

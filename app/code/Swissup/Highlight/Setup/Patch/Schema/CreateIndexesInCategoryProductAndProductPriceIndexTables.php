<?php

namespace Swissup\Highlight\Setup\Patch\Schema;

use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class CreateIndexesInCategoryProductAndProductPriceIndexTables implements SchemaPatchInterface
{
    private SchemaSetupInterface $schemaSetup;

    public function __construct(SchemaSetupInterface $schemaSetup)
    {
        $this->schemaSetup = $schemaSetup;
    }

    public function apply(): self
    {
        $this->schemaSetup->startSetup();

        $config = [
            'catalog_category_product_index' => [
                [
                    'name' => 'HLT_CAT_CTGR_PRD_IDX_POS_PRD_ID_CTGR_ID_STR_ID_VIS',
                    'columns' => [
                        'position',
                        'product_id DESC',
                        'category_id',
                        'store_id',
                        'visibility',
                    ]
                ]
            ],
            'catalog_category_product_index_replica' => [
                [
                    'name' => 'HLT_CAT_CTGR_PRD_IDX_RPL_POS_PRD_ID_CTGR_ID_STR_ID_VIS',
                    'columns' => [
                        'position',
                        'product_id DESC',
                        'category_id',
                        'store_id',
                        'visibility',
                    ]
                ]
            ],
            'catalog_product_index_price' => [
                [
                    'name' => 'HLT_CAT_PRD_IDX_PRICE_MIN_PRICE_ENT_ID',
                    'columns' => [
                        'min_price',
                        'entity_id DESC',
                    ]
                ]
            ],
        ];

        // Create descending indexes to significantly improve queries on large datasets
        $connection = $this->schemaSetup->getConnection();
        foreach ($config as $tableName => $indexes) {
            $tableName = $this->schemaSetup->getTable($tableName);
            foreach ($indexes as $index) {
                $this->createDescendingIndex($tableName, $index['name'], $index['columns']);
            }
        }

        // Loop over dynamic catalog_category_product_index_storeX tables
        $storeSelect = $connection->select()->from($this->schemaSetup->getTable('store'))->where('store_id > 0');
        foreach ($connection->fetchAll($storeSelect) as $store) {
            $indexTable = $this->schemaSetup->getTable('catalog_category_product_index') .
                '_' . \Magento\Store\Model\Store::ENTITY . $store['store_id'];
            if ($connection->isTableExists($indexTable)) {
                foreach ($config['catalog_category_product_index'] as $index) {
                    $indexName = $index['name'] . $store['store_id'];
                    $this->createDescendingIndex($indexTable, $indexName, $index['columns']);
                }
            }

            $replicaTable = $indexTable . '_replica';
            if ($connection->isTableExists($replicaTable)) {
                foreach ($config['catalog_category_product_index_replica'] as $index) {
                    $indexName = $index['name'] . $store['store_id'];
                    $this->createDescendingIndex($replicaTable, $indexName, $index['columns']);
                }
            }
        }

        $this->schemaSetup->endSetup();

        return $this;
    }

    private function createDescendingIndex($table, $name, $columns)
    {
        try {
            $index = implode(', ', $columns);
            $this->schemaSetup->getConnection()->query(
                "CREATE INDEX {$name} ON {$table} ({$index})"
            );
        } catch (\Exception $e) {
            // DB doesn't support DESC indexes :(
        }
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}

<?php

namespace Swissup\Hreflang\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Swissup\Hreflang\Helper\Store as Helper;
use Swissup\Hreflang\Model\Config\Source\ValueStrategy;

class UpdateCustomValue implements DataPatchInterface
{
    private ModuleDataSetupInterface $moduleDataSetup;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $connection = $this->moduleDataSetup->getConnection();
        $tableConfig = $this->moduleDataSetup->getTable('core_config_data');

        $select = $connection->select()
            ->from($tableConfig)
            ->where('path IN (?)', [
                'swissup_hreflang/general/value_strategy',
                'swissup_hreflang/general/custom_locale'
            ]);

        if (!$data = $connection->fetchAll($select)) {
            return $this;
        }

        foreach ($data as &$row) {
            if ($row['path'] == 'swissup_hreflang/general/custom_locale') {
                $row['path'] = Helper::XML_PATH_CUSTOM_VALUE;
            } elseif ($row['path'] == 'swissup_hreflang/general/value_strategy'
                && $row['value'] == 'custom_locale'
            ) {
                $row['value'] = ValueStrategy::CUSTOM_VALUE;
            }
        }

        $connection->insertOnDuplicate(
            $tableConfig,
            $data,
            [
                'path' => new \Laminas\Db\Sql\Expression('VALUES(path)'),
                'value' => new \Laminas\Db\Sql\Expression('VALUES(value)')
            ]
        );

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}

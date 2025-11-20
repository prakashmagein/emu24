<?php
namespace Emu24\CreditLimit\Setup\Patch\Schema;

use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class CreateCreditReportTable implements SchemaPatchInterface
{
    private $schemaSetup;

    public function __construct(SchemaSetupInterface $schemaSetup)
    {
        $this->schemaSetup = $schemaSetup;
    }

    public function apply()
    {
        $setup = $this->schemaSetup;
        $setup->startSetup();

        if (!$setup->tableExists('emu24_creditlimit_report')) {
            $table = $setup->getConnection()->newTable($setup->getTable('emu24_creditlimit_report'))
                ->addColumn('entity_id', Table::TYPE_INTEGER, null, [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary'  => true,
                ], 'Entity ID')
                ->addColumn('customer_id', Table::TYPE_INTEGER, null, [
                    'unsigned' => true,
                    'nullable' => false,
                ], 'Customer ID')
                ->addColumn('regno', Table::TYPE_TEXT, 255, ['nullable' => false], 'Registration Number')
                ->addColumn('company_id', Table::TYPE_TEXT, 255, ['nullable' => true], 'Company ID')
                ->addColumn('company_name', Table::TYPE_TEXT, 255, ['nullable' => true], 'Company Name')
                ->addColumn('credit_limit_amount', Table::TYPE_DECIMAL, '20,4', ['nullable' => true], 'Credit Limit Amount')
                ->addColumn('credit_limit_currency', Table::TYPE_TEXT, 8, ['nullable' => true], 'Credit Limit Currency')
                ->addColumn('credit_score_value', Table::TYPE_TEXT, 32, ['nullable' => true], 'Credit Score Value')
                ->addColumn('credit_score_description', Table::TYPE_TEXT, null, ['nullable' => true], 'Credit Score Description')
                ->addColumn('payload', Table::TYPE_TEXT, '2M', ['nullable' => true], 'Full Response Payload')
                ->addColumn('created_at', Table::TYPE_TIMESTAMP, null, [
                    'nullable' => false,
                    'default'  => Table::TIMESTAMP_INIT,
                ], 'Created At')
                ->addForeignKey(
                    $setup->getFkName('emu24_creditlimit_report', 'customer_id', 'customer_entity', 'entity_id'),
                    'customer_id',
                    $setup->getTable('customer_entity'),
                    'entity_id',
                    Table::ACTION_CASCADE
                )
                ->addIndex(
                    $setup->getIdxName('emu24_creditlimit_report', ['customer_id']),
                    ['customer_id']
                );

            $setup->getConnection()->createTable($table);
        }

        $setup->endSetup();
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }
}

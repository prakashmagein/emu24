<?php

namespace Swissup\ChatGptAssistant\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class MetaPrompts implements DataPatchInterface
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
        $connection->startSetup();
        $entityTable = $this->moduleDataSetup->getTable('swissup_chatgptassistant_prompt');
        $connection->insertMultiple($entityTable, $this->getEntities());
        $connection->endSetup();

        return $this;
    }

    /**
     * Get data to insert into DB table
     *
     * @return array
     */
    private function getEntities() {
        return [
            [
                'name' => 'Product meta title',
                'status' => 1,
                'text' => <<<TEXT
Write a product meta title for {{attribute code="name"}} in {{langCode}} language.
The overall length of the meta title should be not more than 50-60 characters.
Output should be pure text, no tags or quotes.

Here is some additional information about the described product:
product categories: {{categories}}
product attributes: {{attribute code="*"}}
product description: {{attribute code="description"}}
TEXT,
                'field_ids' => 'product-meta-title',
                'default_field_id' => 'product-meta-title'
            ],
            [
                'name' => 'Product meta keywords',
                'status' => 1,
                'text' => <<<TEXT
Create meta keywords for {{attribute code="name"}} in {{langCode}} language.
Separate the words or phrases using a comma.
Place the most important words or phrases at the beginning of the list.
The overall length of the meta keywords should be not more than ten or fifteen unique keywords or phrases.

Here is some additional information about the described product:
product categories: {{categories}}
product attributes: {{attribute code="*"}}
product description: {{attribute code="description"}}
TEXT,
                'field_ids' => 'product-meta-keyword',
                'default_field_id' => 'product-meta-keyword'
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
            \Swissup\ChatGptAssistant\Setup\Patch\Data\InitialPrompts::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}

<?php

namespace Swissup\ChatGptAssistant\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class CategoryPrompts implements DataPatchInterface
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
                'name' => 'Category description',
                'status' => 1,
                'text' => <<<TEXT
Write a short marketing description for the product category "{{attribute code="name"}}" in {{langCode}} language.
The overall length of the description should be not more than 300 words.
Wrap paragraphs with the HTML tag "p".

Here is some additional information about the described category:
child subcategories: {{subcats}}
TEXT,
                'field_ids' => 'category-description',
                'default_field_id' => 'category-description'
            ],
            [
                'name' => 'Category meta description',
                'status' => 1,
                'text' => <<<TEXT
Create HTML meta description for the product category "{{attribute code="name"}}" in {{langCode}} language.
The overall length of the HTML meta description should be not more than 250 characters.
Output should be pure text, no tags or quotes.

Here is some additional information about the described category:
child subcategories: {{subcats}}

The meta description should be based on the category description listed below:
{{attribute code="description"}}
TEXT,
                'field_ids' => 'category-meta-description',
                'default_field_id' => 'category-meta-description'
            ],
            [
                'name' => 'Category meta title',
                'status' => 1,
                'text' => <<<TEXT
Write a meta title for the product category "{{attribute code="name"}}" in {{langCode}} language.
The overall length of the meta title should be not more than 50-60 characters.
Output should be pure text, no tags or quotes.

Here is some additional information about the described category:
child subcategories: {{subcats}}
category description: {{attribute code="description"}}
TEXT,
                'field_ids' => 'category-meta-title',
                'default_field_id' => 'category-meta-title'
            ],
            [
                'name' => 'Category meta keywords',
                'status' => 1,
                'text' => <<<TEXT
Create meta keywords for the product category "{{attribute code="name"}}" in {{langCode}} language.
Separate the words or phrases using a comma.
Place the most important words or phrases at the beginning of the list.
The overall length of the meta keywords should be not more than ten or fifteen unique keywords or phrases.

Here is some additional information about the described category:
child subcategories: {{subcats}}
category description: {{attribute code="description"}}
TEXT,
                'field_ids' => 'category-meta-keywords',
                'default_field_id' => 'category-meta-keywords'
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
            \Swissup\ChatGptAssistant\Setup\Patch\Data\InitialPrompts::class,
            \Swissup\ChatGptAssistant\Setup\Patch\Data\MetaPrompts::class
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

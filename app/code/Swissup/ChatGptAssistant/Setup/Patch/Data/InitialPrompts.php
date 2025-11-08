<?php

namespace Swissup\ChatGptAssistant\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class InitialPrompts implements DataPatchInterface
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
                'name' => 'Product short description',
                'status' => 1,
                'text' => <<<TEXT
Create short product description for {{attribute code="name"}} in {{langCode}} language.
The overall length of the short description should be not more than 150 words.
Wrap paragraphs with the HTML tag "p".

Here is some additional information about the described product:
product categories: {{categories}}
product attributes: {{attribute code="*"}}

The short description should follow the next template example:
paragraph 1: details about the product and why people love this product
paragraph 2: product's characteristics
TEXT,
                'field_ids' => 'product-short-description',
                'default_field_id' => 'product-short-description'
            ],
            [
                'name' => 'Product description',
                'status' => 1,
                'text' => <<<TEXT
Write a product description for {{attribute code="name"}} in {{langCode}} language.
The overall length of the description should be not more than 300 words.

Here is some additional information about the described product:
product categories: {{categories}}
product attributes: {{attribute code="*"}}

The description should follow the next template:
line 1: <h2>product name</h2>
paragraph 1: use of the product, for what people is this product recommended
paragraph 2: product's characteristic
paragraph 3: composition
paragraph 4: conclusion
TEXT,
                'field_ids' => 'product-description',
                'default_field_id' => 'product-description'
            ],
            [
                'name' => 'Product meta description',
                'status' => 1,
                'text' => <<<TEXT
Create HTML meta description for {{attribute code="name"}} in {{langCode}} language.
The overall length of the HTML meta description should be not more than 250 characters.
Output should be pure text, no tags or quotes.

Here is some additional information about the described product:
product categories: {{categories}}
product attributes: {{attribute code="*"}}

The meta description should be based on the product description listed below:
{{attribute code="description"}}
TEXT,
                'field_ids' => 'product-meta-description',
                'default_field_id' => 'product-meta-description'
            ]
        ];
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

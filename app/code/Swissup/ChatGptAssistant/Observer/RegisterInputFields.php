<?php

namespace Swissup\ChatGptAssistant\Observer;

class RegisterInputFields implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $observer->getCollection()
            ->addItem(
                new \Swissup\ChatGptAssistant\Model\InputField([
                    'id' => 'product-short-description',
                    'name' => 'Product: Short Description',
                    'js_config' => [
                        'async' => [
                            'selector' => 'textarea[name="short_description"]',
                            'ctx' => '.catalog-product-edit #container'
                        ]
                    ]
                ])
            )
            ->addItem(
                new \Swissup\ChatGptAssistant\Model\InputField([
                    'id' => 'product-description',
                    'name' => 'Product: Description',
                    'js_config' => [
                        'async' => [
                            'selector' => 'textarea[name="product[description]"], textarea[name="description"], textarea[name="html"]',
                            'ctx' => '.catalog-product-edit'
                        ]
                    ]
                ])
            )
            ->addItem(
                new \Swissup\ChatGptAssistant\Model\InputField([
                    'id' => 'product-meta-description',
                    'name' => 'Product: Meta Description',
                    'js_config' => [
                        'async' => [
                            'selector' => 'textarea[name="product[meta_description]"]',
                            'ctx' => '.catalog-product-edit #container'
                        ],
                        'style' => [
                            'top' => '-31px'
                        ]
                    ]
                ])
            )
            ->addItem(
                new \Swissup\ChatGptAssistant\Model\InputField([
                    'id' => 'product-meta-title',
                    'name' => 'Product: Meta Title',
                    'js_config' => [
                        'async' => [
                            'selector' => 'input[name="product[meta_title]"]',
                            'ctx' => '.catalog-product-edit #container'
                        ],
                        'style' => [
                            'top' => '-31px'
                        ]
                    ]
                ])
            )
            ->addItem(
                new \Swissup\ChatGptAssistant\Model\InputField([
                    'id' => 'product-meta-keyword',
                    'name' => 'Product: Meta Keywords',
                    'js_config' => [
                        'async' => [
                            'selector' => 'textarea[name="product[meta_keyword]"]',
                            'ctx' => '.catalog-product-edit #container'
                        ],
                        'style' => [
                            'top' => '-31px'
                        ]
                    ]
                ])
            )
            ->addItem(
                new \Swissup\ChatGptAssistant\Model\InputField([
                    'id' => 'category-description',
                    'name' => 'Category: Description',
                    'js_config' => [
                        'async' => [
                            'selector' => 'textarea[name="description"], textarea[name="html"]',
                            'ctx' => '.catalog-category-edit'
                        ]
                    ]
                ])
            )
            ->addItem(
                new \Swissup\ChatGptAssistant\Model\InputField([
                    'id' => 'category-meta-description',
                    'name' => 'Category: Meta Description',
                    'js_config' => [
                        'async' => [
                            'selector' => 'textarea[name="meta_description"]',
                            'ctx' => '.catalog-category-edit #container'
                        ],
                        'style' => [
                            'top' => '-31px'
                        ]
                    ]
                ])
            )
            ->addItem(
                new \Swissup\ChatGptAssistant\Model\InputField([
                    'id' => 'category-meta-title',
                    'name' => 'Category: Meta Title',
                    'js_config' => [
                        'async' => [
                            'selector' => 'input[name="meta_title"]',
                            'ctx' => '.catalog-category-edit #container'
                        ],
                        'style' => [
                            'top' => '-31px'
                        ]
                    ]
                ])
            )
            ->addItem(
                new \Swissup\ChatGptAssistant\Model\InputField([
                    'id' => 'category-meta-keywords',
                    'name' => 'Category: Meta Keywords',
                    'js_config' => [
                        'async' => [
                            'selector' => 'textarea[name="meta_keywords"]',
                            'ctx' => '.catalog-category-edit #container'
                        ],
                        'style' => [
                            'top' => '-31px'
                        ]
                    ]
                ])
            );
    }
}

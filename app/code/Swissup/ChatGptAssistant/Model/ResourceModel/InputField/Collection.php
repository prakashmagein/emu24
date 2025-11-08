<?php

namespace Swissup\ChatGptAssistant\Model\ResourceModel\InputField;

use Swissup\ChatGptAssistant\Model\ResourceModel\Prompt\CollectionFactory as PromptCollectionFactory;

class Collection extends \Magento\Framework\Data\Collection
{
    /**
     * Item object class name
     *
     * @var string
     */
    protected $_itemObjectClass = \Swissup\ChatGptAssistant\Model\InputField::class;

    private \Magento\Framework\Event\ManagerInterface $eventManager;

    private PromptCollectionFactory $promptCollectionFactory;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        PromptCollectionFactory $promptCollectionFactory
    ) {
        parent::__construct($entityFactory);
        $this->eventManager = $eventManager;
        $this->promptCollectionFactory = $promptCollectionFactory;
    }

    /**
     * Add an object to the collection
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     * @throws LocalizedException
     */
    public function addItem(\Magento\Framework\DataObject $object)
    {
        if (!$object instanceof $this->_itemObjectClass) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Attempt to add an invalid object')
            );
        }

        return parent::addItem($object);
    }

    /**
     * Load data
     *
     * @param bool $printQuery
     * @param bool $logQuery
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function loadData($printQuery = false, $logQuery = false)
    {
        if ($this->isLoaded()) {
            return $this;
        }

        $this->eventManager->dispatch(
            'swissup_chatgptassistant_input_fields_load_before',
            ['collection' => $this]
        );

        $this->_setIsLoaded(true);

        $this->sortById()
            ->prepareJsConfig()
            ->addPrompts();

        $this->eventManager->dispatch(
            'swissup_chatgptassistant_input_fields_load_after',
            ['collection' => $this]
        );

        return $this;
    }

    /**
     * Sort items by ID
     *
     * @return $this
     */
    private function sortById()
    {
        $items = $this->getItems();

        uasort($items, function ($a, $b) {
            return $a->getId() > $b->getId() ? 1 : -1;
        });

        $this->_items = $items;

        return $this;
    }

    /**
     * Prepare js_config structure
     *
     * @return $this
     */
    private function prepareJsConfig()
    {
        foreach ($this->getItems() as $field) {
            $jsConfig = $field->getJsConfig();
            if (!$jsConfig) {
                $jsConfig = [];
            }

            $jsConfig['id'] = $field->getId();

            if (empty($jsConfig['prompts'])) {
                $jsConfig['prompts'] = [];
            }

            $field->setJsConfig($jsConfig);
        }

        return $this;
    }

    /**
     * Add prompts to each of the fields
     *
     * @return $this
     */
    private function addPrompts()
    {
        $prompts = $this->promptCollectionFactory->create()
            ->addStatusFilter(
                \Swissup\ChatGptAssistant\Model\Prompt::STATUS_ENABLED
            );
        foreach ($prompts as $prompt) {
            if (empty($prompt->getFieldIds())) {
                continue;
            }

            $fieldIds = explode(',', $prompt->getFieldIds());
            foreach ($fieldIds as $fieldId) {
                $field = $this->getItemById($fieldId);
                if (!$field) {
                    continue;
                }

                $field->addPrompt($prompt->toArray(['entity_id', 'name', 'text', 'default_field_id']));
            }
        }

        return $this;
    }
}

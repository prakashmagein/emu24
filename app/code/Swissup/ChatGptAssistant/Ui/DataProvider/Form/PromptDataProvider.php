<?php
namespace Swissup\ChatGptAssistant\Ui\DataProvider\Form;

use Swissup\ChatGptAssistant\Model\ResourceModel\Prompt\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;

class PromptDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    protected DataPersistorInterface $dataPersistor;

    protected array $loadedData;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $this->loadedData = [];
        $items = $this->collection->getItems();
        /** @var \Swissup\ChatGptAssistant\Model\Prompt $item */
        foreach ($items as $item) {
            $this->loadedData[$item->getId()] = $item->getData();
        }

        $data = $this->dataPersistor->get('chatgpt_assistant_prompt');
        if (!empty($data)) {
            $item = $this->collection->getNewEmptyItem();
            $item->setData($data);
            $this->loadedData[$item->getId()] = $item->getData();
            $this->dataPersistor->clear('chatgpt_assistant_prompt');
        }

        return $this->loadedData;
    }
}

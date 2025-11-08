<?php

namespace Swissup\Askit\Ui\DataProvider\Form;

use Swissup\Askit\Model\ResourceModel\Message\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\RequestInterface;

class MessageAssignDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * Escaper
     *
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * Constructor
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param RequestInterface $request
     * @param \Magento\Framework\Escaper $escaper
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        RequestInterface $request,
        \Magento\Framework\Escaper $escaper,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->request = $request;
        $this->escaper = $escaper;

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

        $selected = $this->request->getParam('selected');
        $excluded = $this->request->getParam('excluded');
        $this->loadedData[null] = [
            'selected' => $selected,
            'excluded' => $excluded
        ];

        if (!$selected && !$excluded) {
            return $this->loadedData;
        }

        $collection = $this->getCollection();
        $collection->getSelect()->reset(\Laminas\Db\Sql\Select::WHERE);
        $collection->addFieldToFilter($this->getPrimaryFieldName(), ['in' => $selected]);
        $collection->addFieldToFilter($this->getPrimaryFieldName(), ['nin' => $excluded]);

        $messages = [];
        foreach ($collection as $message) {
            $messageText = $this->escaper->escapeHtml($message->getText());
            $messages[] = "<p>{$messageText}</p>";
        }
        $this->loadedData[null]['messages'] = implode('<hr />', $messages);

        return $this->loadedData;
    }
}

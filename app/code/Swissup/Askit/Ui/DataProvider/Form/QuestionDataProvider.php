<?php

namespace Swissup\Askit\Ui\DataProvider\Form;

use Swissup\Askit\Model\ResourceModel\Message\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Swissup\Askit\Api\Data\MessageInterface;

class QuestionDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
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
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $backendAuthSession;

    /**
     * @param string                              $name
     * @param string                              $primaryFieldName
     * @param string                              $requestFieldName
     * @param CollectionFactory                   $collectionFactory
     * @param DataPersistorInterface              $dataPersistor
     * @param \Magento\Backend\Model\Auth\Session $backendAuthSession
     * @param array                               $meta
     * @param array                               $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->backendAuthSession = $backendAuthSession;
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
        $items = $this->collection->getItems();
        /** @var \Swissup\Askit\Model\Question $item */
        foreach ($items as $item) {
            $this->loadedData[$item->getId()] = $item->getData();
        }

        if (!$this->loadedData) {
            $user = $this->backendAuthSession->getUser();
            $this->loadedData[null] = [
                'status' => MessageInterface::STATUS_APPROVED,
                'customer_name' => "{$user->getFirstname()} {$user->getLastname()}",
                'edit[customer_name]' => true,
                'email' => $user->getEmail(),
                'edit[email]' => true
            ];
        }

        $data = $this->dataPersistor->get('askit_question');
        if (!empty($data)) {
            $item = $this->collection->getNewEmptyItem();
            $item->setData($data);
            $this->loadedData[$item->getId()] = $item->getData();
            $this->dataPersistor->clear('askit_question');
        }

        return $this->loadedData;
    }
}

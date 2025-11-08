<?php

namespace Swissup\Gdpr\Ui\DataProvider\Form;

use Swissup\Gdpr\Model\ResourceModel\Cookie\CustomCollectionFactory;
use Swissup\Gdpr\Model\ResourceModel\Cookie\BuiltInCollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Store\Model\Store;

class CookieProvider extends AbstractProvider
{
    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var DataPersistorInterface
     */
    protected $builtInCollectionFactory;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param RequestInterface $request
     * @param CollectionFactory $collectionFactory
     * @param BuiltInCollectionFactory $builtInCollectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        RequestInterface $request,
        CustomCollectionFactory $collectionFactory,
        BuiltInCollectionFactory $builtInCollectionFactory,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->builtInCollectionFactory = $builtInCollectionFactory;
        $this->dataPersistor = $dataPersistor;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $request, $meta, $data);
    }

    /**
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $collection = $this->collection;
        $cookieId = $this->request->getParam($this->requestFieldName);
        $storeId = $this->request->getParam('store', Store::DEFAULT_STORE_ID);
        $name = $this->request->getParam('name');

        if (!$cookieId && $name) {
            $collection = $this->builtInCollectionFactory->create()
                ->addFilter('name', $name);
        } else {
            $collection->setStoreId($storeId)
                // Need to apply id filter manually 'cos this method is called too early.
                // @see isScopeOverriddenValue
                ->addFieldToFilter($this->primaryFieldName, $cookieId);
        }

        /** @var \Swissup\Gdpr\Model\Cookie $item */
        foreach ($collection->getItems() as $item) {
            $this->loadedData[$item->getId()] = array_merge($item->getData(), [
                'store_id' => $storeId,
            ]);
        }

        $data = $this->dataPersistor->get('gdpr_cookie');
        if (!empty($data)) {
            $item = $this->collection->getNewEmptyItem();
            $item->setData($data);
            $this->loadedData[$item->getId()] = $item->getData();
            $this->dataPersistor->clear('gdpr_cookie');
        }

        return $this->loadedData;
    }

    /**
     * Prepare meta data
     *
     * @param array $meta
     * @return array
     */
    public function getMeta()
    {
        $meta = parent::getMeta();

        $data = $this->getData();
        if (!$data) {
            return $meta;
        }

        $data = current($data);
        $builtIn = $this->builtInCollectionFactory->create()
            ->addFilter('name', $data['name'])
            ->getFirstItem();

        if ($builtIn->getName()) {
            $meta['general']['children']['name']['arguments']['data']['config']['visible'] = false;
        }

        return $meta;
    }

    /**
     * @return array
     */
    protected function getScopeSpecificFields()
    {
        return [
            'general' => [
                'description',
            ],
        ];
    }

    /**
     * @return array
     */
    protected function getNonScopeSpecificFields()
    {
        return [
            'general' => [
                'status',
                'group',
                'name',
            ],
        ];
    }
}

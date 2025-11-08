<?php

namespace Swissup\Gdpr\Ui\DataProvider\Form;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Store\Model\Store;
use Swissup\Gdpr\Model\ResourceModel\CookieGroup\MergedCollectionFactory;
use Swissup\Gdpr\Model\ResourceModel\CookieGroup\BuiltInCollectionFactory;

class CookieGroupProvider extends AbstractProvider
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
     * @var BuiltInCollectionFactory
     */
    protected $builtInCollectionFactory;

    /**
     * Constructor
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param RequestInterface $request
     * @param MergedCollectionFactory $collectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        RequestInterface $request,
        MergedCollectionFactory $collectionFactory,
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
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $groupId = $this->request->getParam($this->requestFieldName);
        $storeId = $this->request->getParam('store', Store::DEFAULT_STORE_ID);

        // Need to apply id filter manually 'cos this method is called too early.
        // @see isScopeOverriddenValue
        $this->addFilter(new \Magento\Framework\Api\Filter([
            'field' => $this->primaryFieldName,
            'value' => $groupId,
        ]));

        $items = $this->collection->setStoreId($storeId)->getItems();
        foreach ($items as $item) {
            $this->loadedData[$item->getId()] = array_merge($item->getData(), [
                'store_id' => $storeId,
            ]);
        }

        $data = $this->dataPersistor->get('gdpr_cookiegroup');
        if (!empty($data)) {
            $item = $this->collection->getNewEmptyItem();
            $item->setData($data);
            $this->loadedData[$item->getId()] = $item->getData();
            $this->dataPersistor->clear('gdpr_cookiegroup');
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
            ->addFilter('code', $data['code'])
            ->getFirstItem();

        if ($builtIn->getCode()) {
            $meta['general']['children']['code']['arguments']['data']['config']['visible'] = false;
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
                'title',
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
                'required',
                'code',
                'sort_order',
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        if ($filter->getField() === $this->primaryFieldName && !$filter->getValue()) {
            $code = $this->request->getParam('code');
            if ($code) {
                $filter->setField('code');
                $filter->setValue($code);
            } else {
                $filter->setValue(-1); // non existing group
            }
        }

        $this->getCollection()->addFieldToFilter(
            $filter->getField(),
            [$filter->getConditionType() => $filter->getValue()]
        );
    }
}

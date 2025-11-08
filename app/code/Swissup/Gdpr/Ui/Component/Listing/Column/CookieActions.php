<?php

namespace Swissup\Gdpr\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Listing\Columns\Column;

class CookieActions extends Column
{
    const URL_PATH_EDIT = 'swissup_gdpr/cookie/edit';
    const URL_PATH_DELETE = 'swissup_gdpr/cookie/delete';

    /**
     * @var \Swissup\Gdpr\Model\ResourceModel\Cookie\CustomCollectionFactory
     */
    private $collection;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Swissup\Gdpr\Model\ResourceModel\Cookie\CustomCollectionFactory $collectionFactory,
        array $components = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$item) {
            $item[$this->getData('name')] = [
                'edit' => [
                    'href' => $this->getContext()->getUrl(
                        static::URL_PATH_EDIT,
                        $this->getEditParams($item)
                    ),
                    'label' => __('Edit')
                ],
            ];

            if (!isset($item['cookie_id'])) {
                continue;
            }

            $item[$this->getData('name')]['delete'] = [
                'href' => $this->getContext()->getUrl(
                    static::URL_PATH_DELETE,
                    [
                        'cookie_id' => $item['cookie_id']
                    ]
                ),
                'label' => __('Delete'),
                'confirm' => [
                    'title' => __('Delete'),
                    'message' => __('Are you sure you want to delete a record?')
                ]
            ];
        }

        return $dataSource;
    }

    /**
     * @param array $item
     * @return array
     */
    private function getEditParams(array $item)
    {
        if (isset($item['cookie_id'])) {
            return ['cookie_id' => $item['cookie_id']];
        }

        // check if cookie is already overriden in custom cookies
        $cookie = $this->collection->getItemByColumnValue('name', $item['name']);
        if ($cookie && $cookie->getId()) {
            return ['cookie_id' => $cookie->getId()];
        }

        return ['name' => $item['name']];
    }
}

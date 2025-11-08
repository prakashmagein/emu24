<?php

namespace Swissup\Gdpr\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Listing\Columns\Column;

class CookieGroupActions extends Column
{
    const URL_PATH_EDIT = 'swissup_gdpr/cookiegroup/edit';
    const URL_PATH_DELETE = 'swissup_gdpr/cookiegroup/delete';

    /**
     * @var \Swissup\Gdpr\Model\ResourceModel\CookieGroup\CustomCollectionFactory
     */
    private $collection;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Swissup\Gdpr\Model\ResourceModel\CookieGroup\CustomCollectionFactory $collectionFactory,
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

            if (!isset($item['group_id'])) {
                continue;
            }

            $item[$this->getData('name')]['delete'] = [
                'href' => $this->getContext()->getUrl(
                    static::URL_PATH_DELETE,
                    [
                        'group_id' => $item['group_id']
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
        if (isset($item['group_id'])) {
            return ['group_id' => $item['group_id']];
        }

        // check if group is already overriden in custom cookies
        $group = $this->collection->getItemByColumnValue('code', $item['code']);
        if ($group && $group->getId()) {
            return ['group_id' => $group->getId()];
        }

        return ['code' => $item['code']];
    }
}

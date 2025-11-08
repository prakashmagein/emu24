<?php

namespace Swissup\Navigationpro\Ui\DataProvider\Form;

class ItemDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Swissup\Navigationpro\Model\Item\Locator\LocatorInterface
     */
    protected $locator;

    /**
     * @var \Swissup\Navigationpro\Ui\DataProvider\Form\MenuDataProvider
     */
    protected $menuDataProvider;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param \Swissup\Navigationpro\Model\ResourceModel\Item\Collection $collection
     * @param \Swissup\Navigationpro\Model\Item\Locator\LocatorInterface $locator
     * @param \Swissup\Navigationpro\Ui\DataProvider\Form\MenuDataProvider $menuDataProvider
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Swissup\Navigationpro\Model\ResourceModel\Item\Collection $collection,
        \Swissup\Navigationpro\Model\Item\Locator\LocatorInterface $locator,
        \Swissup\Navigationpro\Ui\DataProvider\Form\MenuDataProvider $menuDataProvider,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collection;
        $this->locator = $locator;
        $this->menuDataProvider = $menuDataProvider;
        $this->request = $request;
        $this->messageManager = $messageManager;
    }

    public function getData()
    {
        $item = $this->locator->getItem();

        $useRemoteData = "0";
        if ($item->getRemoteEntityId()) {
            $useRemoteData = $item->getUseRemoteData();
            if (null === $useRemoteData) {
                $useRemoteData = "1";
            }
        }

        if ($item->getData('dropdown_settings/use_menu_settings')
            && !$item->getData('dropdown_settings/layout')
        ) {
            try {
                $itemSettings = $item->getData('dropdown_settings');
                $menuData = $this->menuDataProvider->getData();
                $menuData = current($menuData);
                $level = (int)$item->getLevel() - 1;
                $scopes = ['level' . $level, 'default'];

                foreach ($scopes as $scope) {
                    if (empty($menuData['dropdown_settings'][$scope])) {
                        continue;
                    }
                    $itemSettings = array_merge(
                        $itemSettings,
                        $menuData['dropdown_settings'][$scope]
                    );
                    break;
                }

                $item->setData('dropdown_settings', $itemSettings);
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __($e->getMessage()));
            }
        }

        return [
            $item->getId() => array_merge($item->getData(), [
                'store_id' => $item->getStoreId(),
                'use_remote_data' => $useRemoteData
            ])
        ];
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

        if ($this->request->getParam('store')) {
            foreach ($this->getScopeSpecificFieldsMap() as $fieldsets => $fields) {
                $tmp = &$meta;
                foreach (explode('/', $fieldsets) as $fieldset) {
                    if (!isset($tmp[$fieldset]['children'])) {
                        $tmp[$fieldset]['children'] = [];
                    }
                    $tmp = &$tmp[$fieldset]['children'];
                }

                foreach ($fields as $field) {
                    $tmp[$field]['arguments']['data']['config']['service'] = [
                        'template' => 'ui/form/element/helper/service',
                    ];
                    $tmp[$field]['arguments']['data']['config']['disabled'] =
                        !$this->isScopeOverriddenValue($field, $fieldsets);
                }
            }
        }

        $item = $this->locator->getItem();
        if (!$item->getRemoteEntityId()) {
            $meta['main']['children']['general']['children']['use_remote_data']
                ['arguments']['data']['config']['visible'] = false;
        }

        return $meta;
    }

    protected function isScopeOverriddenValue($field, $group)
    {
        $data = $this->locator->getItem()->getData();

        if (empty($data['store_id'])) {
            return false; // all values are from default store view
        }

        if (strpos($group, 'dropdown_settings') !== false) {
            return isset($data['content']['scope']['dropdown_settings'][$field]);
        }

        // @see Swissup\Navigationpro\Model\ResourceModel\Item::_afterLoad
        return isset($data['content']['scope'][$field]);
    }

    /**
     * @return array
     */
    protected function getScopeSpecificFieldsMap()
    {
        return [
            'main/general' => [
                'name',
                'url_path',
            ],
            'main/advanced/html_wrapper' => [
                'html',
            ],
            'main/advanced' => [
                'css_class',
            ],
            'main/dropdown_settings' => [
                'use_menu_settings',
                'width',
                'layout',
                'dropdown_css_class',
            ],
        ];
    }
}

<?php

namespace Swissup\Navigationpro\Ui\DataProvider\Form;

class MenuDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Swissup\Navigationpro\Model\Menu\Locator\LocatorInterface
     */
    protected $locator;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Swissup\Navigationpro\Model\ResourceModel\Menu\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Magento\Framework\App\Config\ValueFactory
     */
    private $configValueFactory;

    /**
     * @var \Swissup\Navigationpro\Model\Menu\Source\Modifiers
     */
    private $cssModifiers;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param \Swissup\Navigationpro\Model\ResourceModel\Menu\Collection $collection
     * @param \Swissup\Navigationpro\Model\Menu\Locator\LocatorInterface $locator
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Swissup\Navigationpro\Model\ResourceModel\Menu\CollectionFactory $collectionFactory
     * @param \Magento\Framework\App\Config\ValueFactory $configValueFactory
     * @param \Swissup\Navigationpro\Model\Menu\Source\Modifiers $cssModifiers
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Swissup\Navigationpro\Model\ResourceModel\Menu\Collection $collection,
        \Swissup\Navigationpro\Model\Menu\Locator\LocatorInterface $locator,
        \Magento\Framework\App\RequestInterface $request,
        \Swissup\Navigationpro\Model\ResourceModel\Menu\CollectionFactory $collectionFactory,
        \Magento\Framework\App\Config\ValueFactory $configValueFactory,
        \Swissup\Navigationpro\Model\Menu\Source\Modifiers $cssModifiers,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collection;
        $this->locator = $locator;
        $this->request = $request;
        $this->collectionFactory = $collectionFactory;
        $this->configValueFactory = $configValueFactory;
        $this->cssModifiers = $cssModifiers;
    }

    public function getData()
    {
        $menu = $this->locator->getMenu();

        // Fill dropdown_settings with default data if needed
        $dropdownSettingKeys = [
            'default',
            'level1',
            'level2',
        ];
        $dropdownSettings = $menu->getData('dropdown_settings');
        foreach ($dropdownSettingKeys as $key) {
            if (empty($dropdownSettings[$key])) {
                $dropdownSettings[$key] = [
                    'width'  => 'small',
                    'layout' => $this->getDefaultDropdownLayout(),
                ];
            }
        }
        $menu->setData('dropdown_settings', $dropdownSettings);

        // Fill item_settings with default data if needed
        $itemSettingKeys = [
            'default',
            'level1',
            'level2',
            'level3',
        ];
        $itemSettings = $menu->getData('item_settings');
        if (!$itemSettings) {
            $itemSettings = [];
        }
        foreach ($itemSettingKeys as $key) {
            if (empty($itemSettings[$key])) {
                $itemSettings[$key] = [
                    'html' => '',
                    'css_class' => '',
                ];
            }
        }
        $menu->setData('item_settings', $itemSettings);

        // prepere config_scopes data
        $config = $this->configValueFactory->create()->getCollection()
            ->addFieldToFilter('path', \Swissup\Navigationpro\Helper\Data::CONFIG_PATH_TOPMENU)
            ->addFieldToFilter('value', $menu->getIdentifier());

        $menu->importConfigScopes($config);
        $this->convertCssClassToModifiers($menu);

        return [
            $menu->getId() => $menu->getData()
        ];
    }

    /**
     * @param \Swissup\Navigationpro\Model\Menu $menu
     * @return void
     */
    private function convertCssClassToModifiers($menu)
    {
        $classes = (string) $menu->getData('css_class');
        $classes = explode(' ', $classes);
        $classes = array_combine($classes, $classes);

        // move some css_classes into modifiers string
        $modifiers = array_keys($this->cssModifiers->toArray());
        $modifiers = array_values(array_intersect($classes, $modifiers));

        // remove modifiers from css_class string
        foreach ($modifiers as $modifier) {
            unset($classes[$modifier]);
        }

        $menu->setData('modifiers', $modifiers);
        $menu->setData('css_class', implode(' ', $classes));
    }

    protected function getDefaultDropdownLayout()
    {
        return json_encode([
            "start" => [
                "size" => 0,
                "rows" => []
            ],
            "center" => [
                "size" => 12,
                "rows" => [[[
                    "id" => uniqid('navpro_'),
                    "size" => "12",
                    "type" => "children",
                    "is_active" => "1",
                    "columns_count" => "1",
                    "children_sort_order" => "",
                    "max_children_count" => "",
                    "levels_per_dropdown" => "1",
                ]]]
            ],
            "end" => [
                "size" => 0,
                "rows" => []
            ]
        ]);
    }

    public function getMeta()
    {
        $meta = parent::getMeta();

        $usedIdentifiers = $this->collectionFactory->create()
            ->addFieldToFilter('menu_id', ['neq' => $this->locator->getMenu()->getId()])
            ->getColumnValues('identifier');

        $meta['general']['children']['identifier']
            ['arguments']['data']['config']['validation']
            ['navpro-validate-unique'] = $usedIdentifiers;

        return $meta;
    }
}

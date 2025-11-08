<?php

namespace Swissup\Navigationpro\Model\Menu;

use Magento\Store\Model\Store;
use Swissup\Navigationpro\Model\Menu\Source\CategoryImportMode;

class Builder
{
    /**
     * @var \Swissup\Navigationpro\Model\Menu
     */
    private $menu;

    /**
     * @var \Swissup\Navigationpro\Model\ItemFactory
     */
    private $itemFactory;

    /**
     * @var \Magento\Widget\Model\Widget\InstanceFactory
     */
    protected $widgetFactory;

    /**
     * @var array
     */
    private $items = [];

    /**
     * @var array
     */
    private $settings = [];

    /**
     * @var array
     */
    private $widgetSettings = [];

    /**
     * @var int
     */
    private $rootCategoryId;

    /**
     * @var int
     */
    private $themeId;

    /**
     * @var array
     */
    private $storeIds = [];

    /**
     * @param \Swissup\Navigationpro\Model\MenuFactory $menuFactory
     * @param \Swissup\Navigationpro\Model\ItemFactory $itemFactory
     * @param \Magento\Widget\Model\Widget\InstanceFactory $widgetFactory
     */
    public function __construct(
        \Swissup\Navigationpro\Model\MenuFactory $menuFactory,
        \Swissup\Navigationpro\Model\ItemFactory $itemFactory,
        \Magento\Widget\Model\Widget\InstanceFactory $widgetFactory
    ) {
        $this->menu = $menuFactory->create();
        $this->itemFactory = $itemFactory;
        $this->widgetFactory = $widgetFactory;

        $this->prepareSettings();
        $this->prepareWidgetSettings();
        $this->prepareItems();
    }

    /**
     * @return self
     */
    protected function prepareSettings()
    {
        return $this;
    }

    /**
     * @return self
     */
    protected function prepareWidgetSettings()
    {
        return $this;
    }

    /**
     * @return self
     */
    protected function prepareItems()
    {
        return $this;
    }

    public function save()
    {
        $settings = $this->getSettings();

        if (!isset($settings['dropdown_settings'])) {
            $settings['dropdown_settings'] = $this->getDefaultDropdownSettings();
        } else {
            $settings['dropdown_settings'] = array_replace_recursive(
                $this->getDefaultDropdownSettings(),
                $settings['dropdown_settings']
            );
        }

        // Prepare dropdown settings
        $dropdownSettings = [];
        foreach ($settings['dropdown_settings'] as $level => $levelSettings) {
            $dropdownSettings[$level] = $levelSettings;
            unset($dropdownSettings[$level]['layout']);

            $region = key($levelSettings['layout']);
            if (is_numeric($region)) {
                $region = 'center';
                $dropdownSettings[$level]['layout'][$region]['rows'] = [$levelSettings['layout']];
            } else {
                $dropdownSettings[$level]['layout'] = $levelSettings['layout'];
            }

            $dropdownSettings[$level]['layout'] =
                $this->prepareDropdownLayoutSettings(
                    $dropdownSettings[$level]['layout']
                );
        }
        unset($settings['dropdown_settings']);

        $this->menu->addData($settings)
            ->setDropdownSettings($dropdownSettings);
        $this->menu->save();

        $this->saveItems(array_filter($this->getItems()));

        $this->afterSave();

        return $this->menu;
    }

    /**
     * @return self
     */
    protected function afterSave()
    {
        if ($this->getWidgetSettings()) {
            $this->saveWidget();
        }

        return $this;
    }

    /**
     * @return void
     */
    protected function saveWidget()
    {
        $menu = $this->getMenu();

        if (!$title = $this->getWidgetSettings('title')) {
            $title = 'NavigationPro ' . $menu->getIdentifier();
        }

        $widget = $this->widgetFactory->create();
        $widget
            ->setType('Swissup\Navigationpro\Block\Widget\Menu')
            ->setCode('navigationpro_menu')
            ->setThemeId($this->getThemeId())
            ->setTitle($title)
            ->setStoreIds($this->getStoreIds())
            ->setSortOrder($this->getWidgetSettings('sort_order'))
            ->setPageGroups($this->getWidgetSettings('page_groups'))
            ->setWidgetParameters(array_merge([
                'identifier' => $menu->getIdentifier(),
            ], $this->getWidgetSettings('params')));

        try {
            $widget->save();
        } catch (\Exception $e) {
        }
    }

    /**
     * Save menu items
     *
     * @param array $items
     */
    private function saveItems($items, $parentItem = null)
    {
        foreach ($items as $itemData) {
            if (isset($itemData['method'])) {
                $method = $itemData['method'];
                if (method_exists($this, $method)) {
                    $this->{$method}($parentItem);
                }
                continue;
            }

            $itemData = array_merge([
                'store_id' => Store::DEFAULT_STORE_ID,
                'menu_id'  => $this->menu->getId(),
            ], $itemData);

            if ($parentItem) {
                $itemData['parent_item'] = $parentItem;
                $itemData['parent_id']   = $parentItem->getId();
            }

            if (isset($itemData['dropdown_settings']['layout'])) {
                $itemData['dropdown_settings']['use_menu_settings'] = 0;

                if (!isset($itemData['dropdown_settings']['width'])) {
                    $itemData['dropdown_settings']['width'] = 'small';
                }

                $layoutSettings = [];
                $region = key($itemData['dropdown_settings']['layout']);
                if (is_numeric($region)) {
                    $region = 'center';
                    $layoutSettings[$region]['rows'] = [$itemData['dropdown_settings']['layout']];
                } else {
                    $layoutSettings = $itemData['dropdown_settings']['layout'];
                }
                $itemData['dropdown_settings']['layout'] =
                    $this->prepareDropdownLayoutSettings($layoutSettings);
            }

            $item = $this->itemFactory->create();
            $item->addData($itemData);
            $item->save();

            if (isset($itemData['items'])) {
                $this->saveItems($itemData['items'], $item);
            }
        }
    }

    /**
     * Import selected category into parentItem
     *
     * @param  int $categoryId
     * @param  int $parentItem
     * @param  string $mode
     */
    private function importCategories($parentItem = null)
    {
        $categoryId = $this->getRootCategoryId();
        if (!$categoryId) {
            return;
        }

        $parentItemId = $parentItem ? $parentItem->getId() : null;

        $this->menu->importCategory(
            $categoryId,
            $parentItemId,
            CategoryImportMode::MODE_CHILDREN
        );
    }

    private function getDefaultDropdownSettings()
    {
        $childrenSettings = [
            'type' => 'children',
            'columns_count' => 1,
            'children_sort_order' => '',
            'max_children_count' => '',
            'levels_per_dropdown' => 1,
        ];

        return [
            'default' => [
                'width' => 'small',
                'layout' => [
                    $childrenSettings,
                ],
            ],
            'level1' => [
                'width' => 'small',
                'position' => 'left',
                'layout' => [
                    $childrenSettings,
                ],
            ],
            'level2' => [
                'width' => 'small',
                'layout' => [
                    $childrenSettings,
                ],
            ],
        ];
    }

    /**
     * Add missing required parametes to the layout array
     *
     * @param  array $layoutSettings
     * @return string
     */
    private function prepareDropdownLayoutSettings($layoutSettings)
    {
        foreach ($layoutSettings as &$regionSettings) {
            $regionSettings['size'] = isset($regionSettings['size']) ? $regionSettings['size'] : "12";
            foreach ($regionSettings['rows'] as &$rowItems) {
                foreach ($rowItems as &$item) {
                    $item['id']        = uniqid('navpro_');
                    $item['size']      = $this->arrayGet($item, 'size', 12);
                    $item['is_active'] = $this->arrayGet($item, 'is_active', 1);
                }
            }
        }
        return json_encode($layoutSettings);
    }

    /**
     * Get the value for requested key.
     *
     * To string is used to work well with magento's UI components (toggle element)
     *
     * @param  array    $array
     * @param  string   $key
     * @param  mixed    $default
     * @param  boolean  $toString
     * @return mixed
     */
    private function arrayGet(array $array, $key, $default = null, $toString = true)
    {
        $value = $default;

        if (isset($array[$key])) {
            $value = $array[$key];
        }

        return $toString ? (string) $value : $value;
    }

    /**
     * Set root catalog category id. Will be used to import categories.
     *
     * @param int $id
     */
    public function setRootCategoryId($id)
    {
        $this->rootCategoryId = $id;

        return $this;
    }

    /**
     * Set root catalog category id. Will be used to import categories.
     *
     * @param int $id
     */
    public function getRootCategoryId()
    {
        return $this->rootCategoryId;
    }

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param array $items
     * @return self
     */
    public function updateItems(array $items)
    {
        $this->items = array_replace_recursive($this->items, $items);

        return $this;
    }

    /**
     * @param array $items
     * @return self
     */
    public function setItems(array $items)
    {
        $this->items = $items;

        return $this;
    }

    /**
     * @param array $item
     * @return self
     */
    public function addItem($key, $item)
    {
        $this->items[$key] = $item;

        return $this;
    }

    /**
     * @return array
     */
    public function getSettings($key = null)
    {
        if ($key !== null) {
            return $this->settings[$key] ?? null;
        }
        return $this->settings;
    }

    /**
     * @param array $settings
     * @return self
     */
    public function updateSettings(array $settings)
    {
        $this->settings = array_replace_recursive($this->settings, $settings);

        return $this;
    }

    /**
     * @param array $settings
     * @return self
     */
    public function setSettings(array $settings)
    {
        $this->settings = $settings;

        return $this;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return self
     */
    public function setSetting($key, $value)
    {
        $this->settings[$key] = $value;

        return $this;
    }

    /**
     * @return array
     */
    public function getWidgetSettings($key = null)
    {
        if ($key !== null) {
            return $this->widgetSettings[$key] ?? null;
        }
        return $this->widgetSettings;
    }

    /**
     * @param array $settings
     * @return self
     */
    public function updateWidgetSettings(array $settings)
    {
        $this->widgetSettings = array_replace_recursive($this->widgetSettings, $settings);

        return $this;
    }

    /**
     * @param array $settings
     * @return self
     */
    public function setWidgetSettings(array $settings)
    {
        $this->widgetSettings = $settings;

        return $this;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return self
     */
    public function setWidgetSetting($key, $value)
    {
        $this->widgetSettings[$key] = $value;

        return $this;
    }

    /**
     * @return int
     */
    public function getThemeId()
    {
        return $this->themeId;
    }

    /**
     * @param int $themeId
     * @return self
     */
    public function setThemeId($themeId)
    {
        $this->themeId = $themeId;

        return $this;
    }

    /**
     * @return array
     */
    public function getStoreIds()
    {
        return $this->storeIds;
    }

    /**
     * @param array $storeIds
     * @return self
     */
    public function setStoreIds(array $storeIds)
    {
        $this->storeIds = $storeIds;

        return $this;
    }

    /**
     * @return \Swissup\Navigationpro\Model\Menu
     */
    public function getMenu()
    {
        return $this->menu;
    }
}

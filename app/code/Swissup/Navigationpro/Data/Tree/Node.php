<?php
namespace Swissup\Navigationpro\Data\Tree;

use Magento\Framework\Data\Tree\Node as TreeNode;
use Swissup\Navigationpro\Model\Menu\Source\ContentDisplayMode;


/**
 * Data tree node wrapper
 */
class Node extends TreeNode
{
    //private const MIN_ROOT_CHILDREN = 8;

    /**
     * @var mixed
     */
    private $dropdownSettings;

    /**
     * @var bool
     */
    private $alreadyHasDropdownSettings = false;

    /**
     * @var mixed
     */
    private $layout;

    /**
     * @var mixed
     */
    private $layoutSettings;

    /**
     * @var bool
     */
    private $hasLayoutSettings = false;

    /**
     * @var mixed
     */
    private $advancedSettings;

    /**
     * @var bool
     */
    private $alreadyHasAdvancedSettings = false;

    /**
     * @return array
     */
    private function getItemDropdownSettings()
    {
        if (!$this->alreadyHasDropdownSettings) {

            $item = $this;

            $settings = $item->getDropdownSettings();

            if (!$settings || !empty($settings['use_menu_settings'])) {

                $settings = $this->getTree()->getMenuDropdownSettings();

                if ($item->getLevel() !== null) {
                    $dropdownLevel = $item->getLevel() + 1;
                } else {
                    $dropdownLevel = 0;
                }

                if (isset($settings['level' . $dropdownLevel])) {
                    $settings = $settings['level' . $dropdownLevel];
                } else {
                    $settings = $settings['default'];
                }
            }

            $this->dropdownSettings = $settings;
            $this->alreadyHasDropdownSettings = true;
        }

        return $this->dropdownSettings;
    }

    /**
     * @return array
     */
    public function getItemAdvancedSettings()
    {
        if (!$this->alreadyHasAdvancedSettings) {

            $item = $this;

            $settings = [
                'html' => '',
                'css_class' => '',
            ];

            $defaultSettings = $this->getTree()->getItemSettings();

            foreach ($settings as $key => $value) {
                $settings[$key] = $item->getData($key);
                if ($settings[$key]) {
                    continue;
                }

                if ($item->getLevel() !== null) {
                    $level = $item->getLevel() + 1;
                } else {
                    $level = 0;
                }

                if (!empty($defaultSettings['level' . $level][$key])) {
                    $settings[$key] = $defaultSettings['level' . $level][$key];
                } elseif (!empty($defaultSettings['default'][$key])) {
                    $settings[$key] = $defaultSettings['default'][$key];
                }
            }
            $this->advancedSettings = $settings;
            $this->alreadyHasAdvancedSettings = true;
        }

        return $this->advancedSettings;
    }

    /**
     * @return integer
     */
    public function getItemLevelsPerDropdown()
    {
        $item = $this;

        if ($item->getParent()->getId()) {
            return $item->getParent()->getLayoutValue('levels_per_dropdown', 1);
        }
        return $item->getTree()->getVisibleLevels() ?: 1;
    }

    public function getChildrenSortOrder(): string
    {
        return (string) $this->getLayoutValue('children_sort_order', '');
    }

    /**
     * @return integer
     */
    public function getMaxChildrenCount()
    {
        $maxChildrenCount = (int) $this->getLayoutValue('max_children_count', 10000);

       // if ($this->getParent() === null) {
           // $maxChildrenCount = max(self::MIN_ROOT_CHILDREN, $maxChildrenCount);
       // }

        return $maxChildrenCount;
    }

    /**
     * @return array
     */
    public function getLayoutSettings()
    {
        if (!$this->hasLayoutSettings) {
            $layout = [];

            $settings = $this->getItemDropdownSettings();
            if (!empty($settings['layout'])) {
                $jsonHelper = $this->getTree()->getJsonHelper();
                $layout = $jsonHelper->jsonDecode($settings['layout']);
            }
            $this->layoutSettings = $layout;
            $this->hasLayoutSettings = true;
        }

        return $this->layoutSettings;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getPreparedLayoutSettings()
    {
        $layout = $this->getLayoutSettings();
        $preparedLayoutSettings = [];

        $renderer = $this->getRenderer();

        foreach ($layout as $regionCode => $region) {
            $rows = [];
            foreach($region['rows'] as $row) {
                $cols = [];
                foreach ($row as $column) {
                    $content = (string) (isset($column['content']) ? $renderer->getFilteredContent($this, $column['content']) : '');
                    $cols[] = [
                        'type' => (string) (isset($column['type']) ? $column['type'] : 'children'),
                        'columns_count' => (int) (isset($column['columns_count']) ? $column['columns_count'] : 0),
                        'content' => $content,
                        'direction' => (string) (isset($column['direction']) ? $column['direction'] : ''),
                        'children_sort_order' => $column['children_sort_order'] ?? '',
                        'max_children_count' => (int) (isset($column['max_children_count']) ? $column['max_children_count'] : 100000),
                        'levels_per_dropdown' => (int) (isset($column['levels_per_dropdown']) ? $column['levels_per_dropdown'] : 1),
                        'id' => (string) $this->getId() . '_' .  (isset($column['id']) ? $column['id'] : uniqid('navpro_')),
                        'size' => (int) (isset($column['size']) ? $column['size'] : 0),
                        'is_active' => (int) (isset($column['is_active']) ? $column['is_active'] : 0),
                    ];
                }
                $rows[] = [
                    'size' => count($cols),
                    'cols' => $cols,
                ];
            }

            $preparedLayoutSettings[] = [
                'id'   => $this->getId() . '-layout_settings', //uniqid('navpro_layout_'),
                'code' => $regionCode,
                'size' => $region['size'],
                'rows' => $rows,
            ];
        }

        return $preparedLayoutSettings;
    }

    /**
     * Returns array of menu item's attributes
     *
     * @return array
     */
    public function getMenuItemAttributes()
    {
        $menuItemClasses = $this->getMenuItemClasses();

        return ['class' => implode(' ', $menuItemClasses)];
    }

    /**
     * Returns array of menu item's classes
     *
     * @return array
     */
    public function getMenuItemClasses()
    {
        $item = $this;
        $classes = ['li-item'];

        $classes[] = 'level' . $item->getLevel();
        $classes[] = $item->getPositionClass();

        $settings = $item->getItemDropdownSettings();
        $classes[] = 'size-' . $settings['width'];

        if ($item->getIsCategory()) {
            $classes[] = 'category-item';
        }

        if ($item->getIsFirst()) {
            $classes[] = 'first';
        }

        if ($item->getIsActive()) {
            $classes[] = 'active';
        } elseif ($item->getHasActive()) {
            $classes[] = 'has-active';
        }

        if ($item->getIsLast()) {
            $classes[] = 'last';
        }

        if ($item->getClass()) {
            $classes[] = $item->getClass();
        }

        if ($item->getCssClass()) {
            $classes[] = $item->getCssClass();
        }

        if ($item->hasDropdownContent()) {
            if ($item->getLevelsPerDropdown() == 1) {
                $classes[] = 'parent';
            }
        }
        if ($item->getLevelsPerDropdown() > 1) {
            $classes[] = 'parent-expanded';
        }

        return $classes;
    }

    /**
     * @return array of string
     */
    public function getItemDropdownContentClasses()
    {
        $item = $this;
        $settings = $item->getItemDropdownSettings();

        $classes = [
            $item->getLevelsPerDropdown() > 1 ? 'navpro-dropdown-expanded' : 'navpro-dropdown'
        ];

        if (!empty($settings['dropdown_css_class'])) {
            $classes[] = $settings['dropdown_css_class'];
        }

        $classes[] = 'navpro-dropdown-level' . ($item->getLevel() + 1);
        $classes[] = 'size-' . $settings['width'];

        $classes = implode(' ', $classes);
        $classes = explode(' ', $classes);
        $classes = array_filter($classes);
        $classes = array_unique($classes);

        return $classes;
    }

    /**
     * @return mixed
     */
    private function hasCache()
    {
        $key = $this->getId();
        return $this->getTree()->hasContentCache($key);
    }

    /**
     * @return mixed
     */
    private function loadCache()
    {
        $key = $this->getId();
        return $this->getTree()->loadContentCache($key);
    }

    /**
     * @param $content
     * @return mixed
     */
    private function saveCache($content)
    {
        $key = $this->getId();
        return $this->getTree()->saveContentCache($key, $content);
    }

    /**
     * Check if item has dropdown content to show
     * @return boolean
     */
    public function hasDropdownContent()
    {
        $item = $this;
        if ($this->hasCache()) {
            return $this->loadCache();
        }

        $layout = $this->getLayoutSettings();

        if (empty($layout)) {
            return false;
        }

        $result = false;
        foreach ($layout as $region) {
            if (!$region['size']) {
                continue;
            }
            foreach ($region['rows'] as $row) {
                $hasVisibleChildren = false;
                foreach ($row as $content) {
                    if (!$content['is_active']
                        || !$content['size']
                        || $content['type'] !== 'children'
                    ) {
                        continue;
                    }

                    if (!$item->hasChildren()) {
                        continue;
                    }

                    $hasVisibleChildren = true;
                    break;
                }

                foreach ($row as $content) {
                    if (!$content['is_active'] || !$content['size']) {
                        continue;
                    }

                    switch ($content['type']) {
                        case 'html':
                            if (empty($content['content'])) {
                                break;
                            }

                            if (!empty($content['display_mode'])
                                && $content['display_mode'] === ContentDisplayMode::MODE_IF_HAS_CHILDREN
                                && !$hasVisibleChildren
                            ) {
                                break;
                            }

                            $result = true;
                            break 3;
                        case 'children':
                            if ($item->hasChildren()) {
                                $result = true;
                                break 3;
                            }
                            break;
                    }
                }
            }
        }

        $this->saveCache($result);

        return $result;
    }

    /**
     * @param $item
     * @param $key
     * @param null $default
     * @param string $scope
     * @return mixed|string|null
     */
    public function getLayoutValue($key, $default = null, $scope = 'children')
    {
        $item = $this;

        if (!$item->getId()) {
            return $default;
        }
        $layout = $this->getLayoutSettings();

        if (empty($layout)) {
            return $default;
        }

        foreach ($layout as $region) {
            if (!$region['size']) {
                continue;
            }
            foreach ($region['rows'] as $row) {
                foreach ($row as $content) {
                    if (!$content['is_active'] || !$content['size']) {
                        continue;
                    }

                    if ($content['type'] === $scope) {
                        if (!isset($content[$key])) {
                            return $default;
                        }

                        return empty($content[$key]) ? $default : $content[$key];
                    }
                }
            }
        }

        return $default;
    }

    /**
     * Recursively generates menu html from data that is specified in $menuTree
     *
     * @param \Swissup\Navigationpro\Data\Tree\Node $menuTreeNode
     * @return string
     */
    public function render()
    {
        /** @var \Swissup\Navigationpro\Data\Tree\Node $menuTreeNode */
        $menuTreeNode = $this;

        /** @var \Swissup\Navigationpro\Data\Tree\Node\HtmlRenderer $renderer */
        $renderer = $this->getRenderer();

        return $renderer->render($menuTreeNode);
    }

    /**
     * @return \Swissup\Navigationpro\Data\Tree\Node\HtmlRenderer
     */
    public function getRenderer()
    {
        return $this->getTree()->getRenderer();
    }

    /**
     * Recursively generates menu html from data that is specified in $menuTree
     *
     * @param \Swissup\Navigationpro\Data\Tree\Node $menuTreeNode
     * @return string
     */
    public function getFlatArray()
    {
        $item = $this;
        $result = [];
        $children = $item->getChildren();


        /** @var \Swissup\Navigationpro\Data\Tree\Node\HtmlRenderer $renderer */
        $renderer = $this->getRenderer();

        foreach ($children as $child) {
            /* @var \Swissup\Navigationpro\Data\Tree\Node  $child */

            $childrenIds = $child->getChildrenIds();

            $result[$child->getId()] = $child->toArray([
                'id',
                'name',
                'url',
                'url_path',
                'class',
                'html',
                'is_category',
                'level',
                /* Other typical and possible Node properties */
                /*
                 'css_class', 'has_active', 'is_active', 'remote_entity', 'is_first', 'is_last', 'position_class',
                 'levels_per_dropdown', 'next_levels_per_dropdown', 'counter', 'has_parent',
                */
            ]);

            $html = $result[$child->getId()]['html'];
            if (!empty($html)) {
                $renderer->getFilteredContent($child, $html);
                $result[$child->getId()]['html'] = $html;
            }

            if ($child->hasDropdownContent()) {
                $subresult = $child->getFlatArray();
                $result = array_merge($result, $subresult);
            }

            $result[$child->getId()]['classes'] = implode(' ', $child->getMenuItemClasses());
            $result[$child->getId()]['childrens'] = $childrenIds;

            $result[$child->getId()]['dropdown'] = [
                'has' => !!$child->hasDropdownContent(),
                'classes' => implode(' ', $child->getItemDropdownContentClasses())
            ];

            $result[$child->getId()]['layout_settings'] = $child->getPreparedLayoutSettings();
        }

        return $result;
    }

    /**
     *
     */
    private function beforeGetChildren()
    {
        $children = $this->_childNodes;
        $parentLevel = $this->getLevel();
        $childLevel = $parentLevel === null ? 0 : $parentLevel + 1;

        $lastLevelsPerDropdown = $this->getNextLevelsPerDropdown() ?: 1;
        $outermostClass = $this->getOutermostClass();

        foreach ($children as $child) {
            /* @var \Swissup\Navigationpro\Data\Tree\Node  $child */
            $child->setLevel($childLevel);

            $currentLevelsPerDropdown = max(
                $child->getItemLevelsPerDropdown(),
                $lastLevelsPerDropdown
            );
            $child->setLevelsPerDropdown($currentLevelsPerDropdown);
            $child->setNextLevelsPerDropdown($currentLevelsPerDropdown - 1);

            if ($childLevel == 0 && $outermostClass) {
                $child->setClass($outermostClass);
            }

            $parentItem = $child->getParent();
            $child->setHasParent(!!$parentItem);
        }
    }

    /**
     * Retrieve node children collection
     *
     * @return \Magento\Framework\Data\Tree\Node\Collection
     */
    public function getChildren()
    {
        $this->beforeGetChildren();
        return $this->_childNodes;
    }

    /**
     * @return array
     */
    public function getChildrenIds()
    {
        $children = $this->getChildren();
        $ids = [];
        foreach ($children as $child) {
            $ids[] = $child->getId();
        }

        return $ids;
    }
}
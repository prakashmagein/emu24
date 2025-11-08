<?php
declare(strict_types=1);

namespace Swissup\Navigationpro\Model\Resolver\DataProvider;

use Swissup\Navigationpro\Data\TreeFactory;
use Swissup\Navigationpro\Data\Tree\Node;
use Swissup\Navigationpro\Data\Tree\NodeFactory;

use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class Menu
{
    /**
     * @var int|null
     */
    private $storeId;

    /**
     * @var int|null
     */
    private $categoryId;

    /**
     * @var \Swissup\Navigationpro\Model\Menu|null
     */
    private $menu;

    /**
     * @var
     */
    private $identifier;

    /**
     * @var string
     */
    private $outermostClass;

    /**
     * @var \Magento\Catalog\Model\Category
     */
    private $currentCategory;

    /**
     * @var \Magento\Framework\DataObject
     */
    private $widgetParameters;

    /**
     * Menu data tree
     *
     * @var \Swissup\Navigationpro\Data\Tree\Node|false
     */
    private $menuTreeRootNode = false;

    /**
     * @var \Swissup\Navigationpro\Data\Tree\NodeFactory
     */
    private $nodeFactory;

    /**
     * @var \Swissup\Navigationpro\Data\TreeFactory
     */
    private $treeFactory;

    /**
     * @var \Swissup\Navigationpro\Model\MenuFactory
     */
    private $menuFactory;

    /**
     * System event manager
     *
     *
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @param \Swissup\Navigationpro\Data\Tree\NodeFactory $nodeFactory
     * @param \Swissup\Navigationpro\Data\TreeFactory $treeFactory
     * @param \Swissup\Navigationpro\Model\MenuFactory $menuFactory
     * @param ManagerInterface $eventManager
     */
    public function __construct(
        \Swissup\Navigationpro\Data\Tree\NodeFactory $nodeFactory,
        \Swissup\Navigationpro\Data\TreeFactory $treeFactory,
        \Swissup\Navigationpro\Model\MenuFactory $menuFactory,
        ManagerInterface $eventManager
    ) {
        $this->nodeFactory = $nodeFactory;
        $this->treeFactory = $treeFactory;
        $this->menuFactory = $menuFactory;
        $this->eventManager = $eventManager;
    }

    /**
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        $this->storeId = $storeId;
        return $this;
    }

    /**
     * @param int|null $categoryId
     * @return $this
     */
    public function setCategoryId($categoryId)
    {
        $this->categoryId = $categoryId;
        return $this;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setIdentifier($id)
    {
        $this->identifier = $id;
        $this->menu = null;
        $this->menuTreeRootNode = false;

        return $this;
    }

    /**
     * @param int $menu
     * @return $this
     */
    public function setMenu($menu)
    {
        $this->menu = $menu;
        return $this;
    }

    /**
     * @return \Swissup\Navigationpro\Model\Menu
     */
    public function getMenu()
    {
        if (!$this->menu && $this->identifier) {
            $this->menu = $this->menuFactory->create()
                ->load($this->identifier, 'identifier');
        }
        return $this->menu;
    }

    /**
     * @param $category
     * @return $this
     */
    public function setCurrentCategory($category)
    {
        $this->currentCategory = $category;
        return $this;
    }

    /**
     * @param $outermostClass
     * @return $this
     */
    public function setOutermostClass($outermostClass)
    {
        $this->outermostClass = $outermostClass;

        if ($this->menuTreeRootNode) {
            $this->menuTreeRootNode->setOutermostClass($this->outermostClass);
        }

        return $this;
    }

    /**
     * @param $parameters
     * @return $this
     */
    public function setWidgetParameters($parameters)
    {
        $this->widgetParameters = $parameters;
        return $this;
    }

    /**
     * Get menu html.
     *
     * @return string
     */
    public function render()
    {
        $menuTreeRootNode = $this->getTreeRootNode();

        $html = $menuTreeRootNode->render();

        $transportObject = new \Magento\Framework\DataObject(['html' => $html]);
        $this->eventManager->dispatch(
            'swissup_navigationpro_menu_gethtml_after',
            [
                'menu' => $menuTreeRootNode,
                'transportObject' => $transportObject
            ]
        );
        $html = $transportObject->getHtml();

        return $html;
    }

    /**
     * @return false|Node
     */
    private function getTreeRootNode()
    {
        if ($this->menuTreeRootNode === false) {

            $menuTreeRootNode = $this->nodeFactory->create(
                [
                    'data' => [],
                    'idField' => 'root',
                    'tree' => $this->treeFactory->create()
                ]
            );

            $menu = $this->getMenu();

            $currentCategory = $this->currentCategory;
            $categoryId = $this->categoryId;

            $this->eventManager->dispatch(
                'swissup_navigationpro_menu_prepare',
    //            \Swissup\Navigationpro\Observer\PrepareMenuItems::execute
    //              |-> \Swissup\Navigationpro\Observer\TransformToActiveBranch::execute
                [
                    'menu_tree_root_node' => $menuTreeRootNode,
                    'menu' => $menu,
                    'store_id' => $this->storeId,
                    'current_category' => $currentCategory,
                    'category_id' => $categoryId,
                    'widget_parameters' => $this->widgetParameters,
                ]
            );

            /* @var \Swissup\Navigationpro\Data\Tree $menuTree */
            $menuTree = $menuTreeRootNode->getTree();
            $this->prepareTree($menuTree);

            $this->eventManager->dispatch(
                'swissup_navigationpro_menu_gethtml_before',
                [
                    'menu_tree_root_node' => $menuTreeRootNode,
    //                    'block' => $this->block,
    //                    'request' => $this->getRequest()
                ]
            );

            if (!empty($this->outermostClass)) {
                $menuTreeRootNode->setOutermostClass($this->outermostClass);
            }

            $this->menuTreeRootNode = $menuTreeRootNode;
        }

        return $this->menuTreeRootNode;
    }

    /**
     * @param \Swissup\Navigationpro\Data\Tree $menuTree
     */
    private function prepareTree($menuTree)
    {
        $menu = $this->getMenu();

        if (!$menuTree->hasMenuDropdownSettings()) {
            $menuTree->setMenuDropdownSettings(
                $menu->getDropdownSettings()
            );
        }

        if (!$menuTree->hasVisibleLevels() && $this->widgetParameters) {
            $visibleLevels = $this->widgetParameters->getData('visible_levels');
            $menuTree->setVisibleLevels($visibleLevels);
        }

        if (!$menuTree->hasItemSettings()) {
            $menuTree->setItemSettings($menu->getItemSettings());
        }

        if (!$menuTree->hasIdentifier()) {
            $menuTree->setIdentifier($menu->getId());
        }

        $ulClasses = [];
        if ($this->widgetParameters) {
            $ulClasses[] = $this->widgetParameters->getData('css_class');
        }
        $ulClasses[] = $menu->getCssClass();
        $ulClasses = implode(' ', $ulClasses);
        $menuTree->setUlCssClasses($ulClasses);

        if ($this->widgetParameters) {
            $orientation = $this->widgetParameters->getData('orientation');
            if ($orientation) {
                $menuTree->setOrientation($orientation);
            }

            $side = $this->widgetParameters->getData('dropdown_side');
            if ($side) {
                $menuTree->setDropdownSide($side);
            }

            $theme = $this->widgetParameters->getData('theme');
            if ($theme) {
                $menuTree->setTheme($theme);
            }

            $isShowActiveBranch = $this->widgetParameters->getData('show_active_branch');
            $menuTree->setIsShowActiveBranch($isShowActiveBranch);

            $navClasses = $this->widgetParameters->getData('nav_css_class');
            $menuTree->setNavCssClasses($navClasses);
        }
    }

    /**
     * @return \Swissup\Navigationpro\Data\Tree
     */
    public function getTree()
    {
        return $this->getTreeRootNode()->getTree();
    }

    /**
     * @return array|bool
     */
    public function getAllUlCssClasses()
    {
        return $this->getTree()->getAllUlCssClasses();
    }

    /**
     * Get css classes for the UL element
     *
     * @return array
     */
    public function getUlCssClasses()
    {
        return $this->getTree()->getUlCssClasses();
    }

    /**
     * @param array|string $classes
     * @return array of string
     */
    private function prepareCssClassesString($classes)
    {
        if (is_array($classes)) {
            $classes = implode(' ', $classes);
        }

        $classes = explode(' ', $classes);
        $classes = array_unique($classes);
        $classes = array_filter($classes);

        return $classes;
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function getData(): array
    {
        $menuTreeRootNode = $this->getTreeRootNode();
        $result = $menuTreeRootNode->getFlatArray();

        $items = [];
        foreach ($result as $item) {
            $items[] = [
                'id' => $item['id'],
                'name' => $item['name'],
                'url' => $item['url'],
                'url_path' => $item['url_path'],
                'classes' => $this->prepareCssClassesString(isset($item['classes']) ? $item['classes'] : ''),
                'link' => [
                    'classes' => $this->prepareCssClassesString(isset($item['class']) ? $item['class'] : '')
                ],
                'html' => isset($item['html']) ? $item['html'] : '',
//                'is_active' => $item['is_active'],
                'is_category' => $item['is_category'],
                'level' => $item['level'],
                'childrens' => $item['childrens'],
                'dropdown' => [
                    'has' => isset($item['dropdown']['has']) ? $item['dropdown']['has'] : false,
                    'classes' => $this->prepareCssClassesString(
                        isset($item['dropdown']['classes']) ? $item['dropdown']['classes'] : []
                    )
                ],
                'layout_settings' =>  isset($item['layout_settings']) ? $item['layout_settings'] : [],
            ];
        }

        $data = [
            'nav' => [
                'classes' => $this->prepareCssClassesString($this->getTree()->getNavCssClasses()),
                'ul' => [
                    'classes' => $this->prepareCssClassesString($this->getAllUlCssClasses()),
                    'items' => $items,
                    'total_count' => count($items)
                ]
            ]
        ];

        return $data;
    }
}

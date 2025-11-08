<?php

namespace Swissup\Navigationpro\Data\Tree\Node;

use Swissup\Navigationpro\Model\Menu\Source\ChildrenSortOrder;

class HtmlRenderer implements RendererInterface {

    /**
     * @var \Swissup\Navigationpro\Model\Template\Filter
     */
    private $filter;

    /**
     * Escaper
     *
     * @var \Magento\Framework\Escaper
     */
    private $escaper;

    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;

    /**
     * @param \Swissup\Navigationpro\Model\Template\Filter $filter
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\App\State $appState
     */
    public function __construct(
        \Swissup\Navigationpro\Model\Template\Filter $filter,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\App\State $appState
    ) {
        $this->filter = $filter;
        $this->escaper = $escaper;
        $this->appState = $appState;
    }

    /**
     * @param \Swissup\Navigationpro\Data\Tree\Node $item
     * @param $content
     * @return string|string[]
     */
    private function filter(\Swissup\Navigationpro\Data\Tree\Node $item, $content)
    {
        return $this->filter
            ->setItem($item)
            ->setVariables([
                'item' => $item,
                'remote_entity' => $item->getRemoteEntity(),
            ])
            ->filter($content);
    }

    /**
     * @param \Swissup\Navigationpro\Data\Tree\Node $item
     * @param $content
     * @return string|string[]
     * @throws \Exception
     */
    public function getFilteredContent(\Swissup\Navigationpro\Data\Tree\Node $item, $content)
    {
        $result = '';
        $renderer = $this;

        $this->appState->emulateAreaCode(
            \Magento\Framework\App\Area::AREA_FRONTEND,
            function() use ($renderer, $item, $content, &$result) {
                $result = $renderer->filter($item, $content);
            }
        );

        return $result;
    }

    /**
     * Escape string for HTML context.
     *
     * AllowedTags will not be escaped, except the following: script, img, embed,
     * iframe, video, source, object, audio
     *
     * @param string|array $data
     * @param array|null $allowedTags
     * @return string|array
     */
    private function escapeHtml($data, $allowedTags = null)
    {
        return $this->escaper->escapeHtml($data, $allowedTags);
    }

    /**
     * Generates a string to use as item name
     *
     * @return string
     */
    private function getRenderedItemName(\Swissup\Navigationpro\Data\Tree\Node $item)
    {
        $settings = $item->getItemAdvancedSettings();

        if (!empty($settings['html'])) {
            return $this->filter($item, $settings['html']);
        }

        return '<a href="' . $item->getUrl() . '" class="' . $item->getClass() . '">'
            . '<span>'
                . $this->escapeHtml($item->getName())
            . '</span>'
        . '</a>';
    }

    /**
     * Generates string with all attributes that should be present in menu item element
     *
     * @return string
     */
    public function getRenderedMenuItemAttributes(\Swissup\Navigationpro\Data\Tree\Node $item)
    {
        $html = '';
        $attributes = $item->getMenuItemAttributes();
        foreach ($attributes as $attributeName => $attributeValue) {
            $html .= ' ' . $attributeName . '="' . str_replace('"', '\"', $attributeValue) . '"';
        }

        return $html;
    }

    /**
     * Add sub menu HTML code for current menu item
     *
     * @return string HTML code
     */
    private function getRenderedDropdownContent(\Swissup\Navigationpro\Data\Tree\Node $item)
    {
        $html = '';
        if (!$item->hasDropdownContent()) {
            return $html;
        }

        $dropdownContentClassses = implode(' ', $item->getItemDropdownContentClasses());
        $html .= '<div class="' . $dropdownContentClassses . '" data-level="' . $item->getLevel() . '">';
        $html .= '<div class="navpro-dropdown-inner">';

        $layout = $item->getLayoutSettings();

        foreach ($layout as $regionCode => $region) {
            if (!$region['size']) {
                continue;
            }

            foreach ($region['rows'] as $row) {
                $rowHtml = '';
                foreach ($row as $column) {
                    if (!$column['is_active'] || !$column['size']) {
                        continue;
                    }

                    $columnHtml = '';
                    switch ($column['type']) {
                        case 'html':
                            if (empty($column['content'])) {
                                continue 2;
                            }
                            $columnHtml .= $this->filter($item, $column['content']);
                            break;
                        case 'children':
                            if (!$item->hasChildren()) {
                                continue 2;
                            }

                            $classes = ['children'];
                            $columnsCount = 1;
                            if (!empty($column['columns_count']) && $column['columns_count'] > 1) {
                                $columnsCount = $column['columns_count'];
                                $classes[] = 'multicolumn';
                                $classes[] = 'multicolumn-' . $columnsCount;
                            }
                            if (!empty($column['direction']) && $column['direction'] === 'vertical') {
                                $classes[] = 'vertical';
                            }

                            $columnHtml .= '<ul class="' . implode(' ', $classes) . '"'
                                . ' data-columns="' . $columnsCount . '">';
                            $columnHtml .= $this->render($item);
                            $columnHtml .= '</ul>';
                            break;
                    }

                    if ($columnHtml) {
                        $rowHtml .= '<div class="navpro-col navpro-col-' . $column['size'] . '">';
                        $rowHtml .= $columnHtml;
                        $rowHtml .= '</div>';
                    }
                }
                if ($rowHtml) {
                    $html .= '<div class="navpro-row gutters">';
                    $html .= $rowHtml;
                    $html .= '</div>';
                }
            }
        }

        $html .= '</div>';

        if ($item->getLevel() == 0) {
            $html .= '<span class="navpro-shevron"></span>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Renders li
     *
     * @param \Swissup\Navigationpro\Data\Tree\Node $item
     * @return string
     */
    private function renderLi(\Swissup\Navigationpro\Data\Tree\Node $item)
    {
        $html = '';
        $html .= '<li ' . $this->getRenderedMenuItemAttributes($item) . '>';
        $html .= $this->getRenderedItemName($item)
            . $this->getRenderedDropdownContent($item)
            . '</li>';

        return $html;
    }

    /**
     * @param \Swissup\Navigationpro\Data\Tree\Node $item
     * @return string
     */
    private function renderShopAllLi(\Swissup\Navigationpro\Data\Tree\Node $item)
    {
        $html = '<li class="li-item level' . $item->getLevel() . ' navpro-shop-all">'
            . '<a href="' . $item->getParent()->getUrl() . '">'
                . '<span>'
                    . __('Shop All')
                . '</span>'
            . '</a>'
        . '</li>';

        return $html;
    }

    /**
     * Renders menu
     *
     * @param \Swissup\Navigationpro\Data\Tree\Node $item
     * @return string
     */
    public function render(\Swissup\Navigationpro\Data\Tree\Node $item)
    {
        $html = '';

        $children = $item->getChildren()->getNodes();
        if ($item->getChildrenSortOrder() === ChildrenSortOrder::ALPHA) {
            usort($children, function ($a, $b) {
                return $a->getName() <=> $b->getName();
            });
        }

        $counter = 1;
        $parentPositionClass = $item->getPositionClass();
        $itemPositionClassPrefix = $parentPositionClass ? $parentPositionClass . '-' : 'nav-';
        $childrenCount = count($children);
        $maxItemsCount = (int) $item->getMaxChildrenCount();
        foreach ($children as $child) {
            $child->setIsFirst($counter === 1);
            $child->setIsLast($counter === $childrenCount);
            $child->setPositionClass($itemPositionClassPrefix . $counter);

            /* @var \Swissup\Navigationpro\Data\Tree\Node  $child */
            $hasParent = $child->getHasParent();
            if ($counter++ > $maxItemsCount && $hasParent) {
                $html .= $this->renderShopAllLi($child);
                break;
            }

            $html .= $this->renderLi($child);
        }

        return $html;
    }
}

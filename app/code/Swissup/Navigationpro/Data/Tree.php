<?php
namespace Swissup\Navigationpro\Data;

use Magento\Framework\Data\Tree as ParentTree;
use Magento\Framework\Data\Tree\Node\Collection as NodeCollection;


/**
 * Data tree node wrapper
 */
class Tree extends ParentTree
{
    /**
     * @var string
     */
    private $identifier;

    /**
     * @var
     */
    private $menuDropdownSettings;

    /**
     * @var bool
     */
    private $hasMenuDropdownSettings = false;

    /**
     * @var
     */
    private $visibleLevels;

    /**
     * @var bool
     */
    private $hasVisibleLevels = false;

    /**
     * @var
     */
    private $itemSettings;

    /**
     * @var bool
     */
    private $hasItemSettings = false;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;

    /**
     * @var \Swissup\Navigationpro\Data\Tree\Node\RendererInterface
     */
    private $renderer;

    /**
     * Array of hasDropdownContent check results
     * @var array
     */
    private $hasDropdownContentCache = [];

    /**
     * @var string
     */
    private $ulCssClasses;

    /**
     * @var string
     */
    private $navCssClasses;

    /**
     * @var bool|array
     */
    private $allUlCssClasses = false;

    /**
     * @var string
     */
    private $orientation;

    /**
     * @var string
     */
    private $dropdownSide;

    /**
     * @var string
     */
    private $theme;

    /**
     * @var bool
     */
    private $isShowActiveBranch = false;

    /**
     * @param \Swissup\Navigationpro\Data\Tree\Node\RendererInterface $renderer
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        \Swissup\Navigationpro\Data\Tree\Node\RendererInterface $renderer,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        parent::__construct();
        $this->renderer = $renderer;
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * @return \Swissup\Navigationpro\Data\Tree\Node\RendererInterface
     */
    public function getRenderer()
    {
        return $this->renderer;
    }

    /**
     * @return \Magento\Framework\Json\Helper\Data
     */
    public function getJsonHelper()
    {
        return $this->jsonHelper;
    }

    /**
     * @return $this
     */
    public function hasIdentifier()
    {
        return !empty($this->identifier);
    }

    /**
     * @param $identifier
     * @return $this
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param $menuDropdownSettings
     * @return $this
     */
    public function setMenuDropdownSettings($menuDropdownSettings)
    {
        $this->menuDropdownSettings = $menuDropdownSettings;
        $this->hasMenuDropdownSettings = true;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasMenuDropdownSettings()
    {
        return $this->hasMenuDropdownSettings;
    }

    /**
     * @return mixed
     */
    public function getMenuDropdownSettings()
    {
        return $this->menuDropdownSettings;
    }

    /**
     * @param $visibleLevels
     * @return $this
     */
    public function setVisibleLevels($visibleLevels)
    {
        $this->visibleLevels = $visibleLevels;
        $this->hasVisibleLevels = true;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getVisibleLevels()
    {
        return $this->visibleLevels;
    }

    /**
     * @return bool
     */
    public function hasVisibleLevels()
    {
        return $this->hasVisibleLevels;
    }

    /**
     * @param $itemSettings
     * @return $this
     */
    public function setItemSettings($itemSettings)
    {
        $this->itemSettings = $itemSettings;
        $this->hasItemSettings = true;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasItemSettings()
    {
        return $this->hasItemSettings;
    }

    /**
     * @return mixed
     */
    public function getItemSettings()
    {
        return $this->itemSettings;
    }

    /**
     * @param $key
     * @return bool
     */
    public function hasContentCache($key)
    {
        return isset($this->hasDropdownContentCache[$key]);
    }

    /**
     * @param $key
     * @return string
     */
    public function loadContentCache($key)
    {
        return $this->hasDropdownContentCache[$key];
    }

    /**
     *
     * @param $key
     * @param $content
     * @return string
     */
    public function saveContentCache($key, $content)
    {
        return $this->hasDropdownContentCache[$key] = $content;
    }

    /**
     * @param string $ulCssClasses
     * @return $this
     */
    public function setUlCssClasses($ulCssClasses)
    {
        $this->ulCssClasses = $ulCssClasses;
        return $this;
    }

    /**
     * @return array|bool
     */
    public function getAllUlCssClasses()
    {
        if ($this->allUlCssClasses === false) {
            $classes = $this->ulCssClasses;

            if (strpos($classes, 'navpro-slideout') !== false &&
                strpos($classes, 'navpro-stacked') !== false &&
                strpos($classes, 'navpro-click') === false
            ) {
                $classes .= ' navpro-click';
            }
            $classes = explode(' ', $classes);
            $classes = array_unique($classes);
            $classes = array_filter($classes);

            $this->allUlCssClasses = $classes;
        }

        return $this->allUlCssClasses;
    }

    /**
     * Get css classes for the UL element
     *
     * @return array
     */
    public function getUlCssClasses()
    {
        $classes = $this->getAllUlCssClasses();
        $classes = implode(' ', $classes);

        $classes = ' ' . $classes . ' ';
        $classesToRemove = [
            'dropdown-left',
            'dropdown-right',
            'dropdown-top',
            'navpro-accordion',
            'navpro-slideout',
            'navpro-vertical',
            'navpro-horizontal',
            'navpro-theme-air',
            'navpro-theme-compact',
            'navpro-theme-dark-dropdown',
            'navpro-theme-dark',
            'navpro-theme-flat',
            'navpro-effect-none',
            'navpro-effect-fade',
            'navpro-effect-slidein',
            'navpro-effect-slideout',
        ];
        foreach ($classesToRemove as $class) {
            $classes = preg_replace("/\s" . $class . "\s/", ' ', $classes);
        }

        $classes = preg_replace('/\s+/', ' ', $classes);
        $classes = trim($classes);

        return explode(' ', $classes);
    }

    /**
     * @param string $orientation
     * @return $this
     */
    public function setOrientation($orientation)
    {
        $this->orientation = $orientation;
        return $this;
    }

    /**
     * @return string
     */
    private function getOrientation()
    {
        $classes = $this->getAllUlCssClasses();
        $classes = implode(' ', $classes);

        if (strpos($classes, 'navpro-vertical') !== false ||
            strpos($classes, 'navpro-accordion') !== false
        ) {
            return 'vertical';
        }

        $result = $this->orientation;
        if ($result) {
            return $result;
        }

        if (strpos($classes, 'navpro-horizontal') === false &&
            strpos($classes, 'navpro-slideout') !== false
        ) {
            return 'vertical';
        }

        return 'horizontal';
    }

    /**
     * @return mixed|string
     */
    private function getDropdownPositionForFirstLevel()
    {
        $result = 'center';
        $validPositions = ['left', 'right', 'center'];
        $settings = $this->getMenuDropdownSettings();

        if (isset($settings['level1']['position']) &&
            in_array($settings['level1']['position'], $validPositions)
        ) {
            $result = $settings['level1']['position'];
        }

        return $result;
    }

    /**
     * @param $side
     * @return $this
     */
    public function setDropdownSide($side)
    {
        $this->dropdownSide = $side;
        return $this;
    }

    /**
     * @return array
     */
    private function getDropdownSide()
    {
        $result = [
            'x' => 'right',
            'y' => 'bottom',
        ];

        $side = $this->dropdownSide;
        if ($side) {
            if (in_array($side, ['left', 'right'])) {
                $result['x'] = $side;
            } elseif (in_array($side, ['top', 'bottom'])) {
                $result['y'] = $side;
            }
            return $result;
        }

        $classes = $this->getAllUlCssClasses();
        $classes = implode(' ', $classes);

        if (strpos($classes, 'dropdown-left') !== false) {
            $result['x'] = 'left';
        }
        if (strpos($classes, 'dropdown-top') !== false) {
            $result['y'] = 'top';
        }

        return $result;
    }

    /**
     * @param $theme
     * @return $this
     */
    public function setTheme($theme)
    {
        $this->theme = $theme;
        return $this;
    }

    /**
     * @param bool $status
     * @return $this
     */
    public function setIsShowActiveBranch($status)
    {
        $this->isShowActiveBranch = (bool) $status;
        return $this;
    }

    /**
     * @return array
     */
    public function getDropdownPositionConfig()
    {
        $config = [];
        $side = $this->getDropdownSide();

        $my = 'left';
        $at = 'right';

        if ($side['x'] === 'left') {
            $my = 'right';
            $at = 'left';
        }

        if ($side['y'] === 'top') {
            $my .= ' bottom';
            $at .= ' bottom';
        } else {
            $my .= ' top';
            $at .= ' top';
        }

        $position = [
            'my' => $my,
            'at' => $at,
        ];

        // Set level0 position
        if ($this->getOrientation() === 'vertical') {
            $config['level0']['position'] = $position;
        } else {
            $horizontalAlignment = $this->getDropdownPositionForFirstLevel();
            if ($side['y'] === 'top') {
                $config['level0']['position'] = [
                    'my' => $horizontalAlignment . ' bottom',
                    'at' => $horizontalAlignment . ' top',
                ];
            } else {
                $config['level0']['position'] = [
                    'my' => $horizontalAlignment . ' top',
                    'at' => $horizontalAlignment . ' bottom',
                ];
            }
        }

        // set other levels position
        $config['position'] = $position;

        return $config;
    }

    /**
     * @param string $navCssClasses
     * @return $this
     */
    public function setNavCssClasses($navCssClasses)
    {
        $this->navCssClasses = $navCssClasses;
        return $this;
    }

    /**
     * Get CSS classes for the NAV element
     *
     * @return string[]
     */
    public function getNavCssClasses()
    {
        $classes = explode(' ', (string) $this->navCssClasses);

        $orientation = $this->getOrientation();
        if ($orientation === 'accordion') {
            $classes[] = 'orientation-vertical';
            $classes[] = 'navpro-accordion';
        } else {
            $classes[] = 'orientation-' . $orientation;
        }

        $classes[] = 'dropdown-level0-stick-' . $this->getDropdownPositionForFirstLevel();

        $side = $this->getDropdownSide();
        $classes[] = 'dropdown-' . $side['x'];
        $classes[] = 'dropdown-' . $side['y'];

        if ($this->theme) {
            $classes[] = 'navpro-theme-' . $this->theme;
        }

        if ($this->isShowActiveBranch) {
            $classes[] = 'navpro-active-branch';
        }

        $ulCssClasses = $this->getAllUlCssClasses();
        $ulCssClasses = implode(' ', $ulCssClasses);

        if (strpos($ulCssClasses, 'navpro-linkbar') === false
            && strpos($ulCssClasses, 'navpro-untransformable') === false
        ) {
            $classes[] = 'navpro-transformable';
        }

        $classesToCopy = [
            'navpro-accordion',
            'navpro-slideout',
            'navpro-effect-none',
            'navpro-effect-fade',
            'navpro-effect-slidein',
            'navpro-effect-slideout',
        ];
        if (!$this->theme) {
            $classesToCopy = array_merge($classesToCopy, [
                'navpro-theme-air',
                'navpro-theme-compact',
                'navpro-theme-dark-dropdown',
                'navpro-theme-dark',
                'navpro-theme-flat',
            ]);
        }

        $ulCssClasses = ' ' . $ulCssClasses . ' ';
        foreach ($classesToCopy as $classToCopy) {
            if (preg_match("/\s" . $classToCopy . "\s/", $ulCssClasses)) {
                $classes[] = $classToCopy;
            }
        }

        $classes = array_unique($classes);
        $classes = array_filter($classes);

        return $classes;
    }
}

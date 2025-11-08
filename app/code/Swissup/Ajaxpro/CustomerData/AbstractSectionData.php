<?php

namespace Swissup\Ajaxpro\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;

abstract class AbstractSectionData extends \Magento\Framework\DataObject implements SectionSourceInterface
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var \Magento\Framework\View\Layout\BuilderFactory
     */
    protected $layoutBuilderFactory;

    /**
     * @var \Magento\Framework\View\Page\Config
     */
    protected $pageConfig;

    /**
     * @var \Magento\Framework\View\Page\Layout\Reader
     */
    protected $pageLayoutReader;

    /**
     * @var \Swissup\Ajaxpro\Helper\Config
     */
    protected $configHelper;

    /**
     * Layout
     *
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $contextLayout;

    /**
     *
     * @var array
     */
    protected $layouts = [];

    /**
     * @var array
     */
    private $handles = [];

    /**
     * @var array
     */
    private $blocks = [];

    /**
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Magento\Framework\View\Layout\BuilderFactory $layoutBuilderFactory
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\View\Page\Layout\Reader $pageLayoutReader
     * @param \Swissup\Ajaxpro\Helper\Config $configHelper
     * @param array $data
     * @codeCoverageIgnore
     */
    public function __construct(
        \Swissup\Ajaxpro\Model\View\LayoutFactory $layoutFactory,
        \Magento\Framework\View\Layout\BuilderFactory $layoutBuilderFactory,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\View\Page\Layout\Reader $pageLayoutReader,
        \Swissup\Ajaxpro\Helper\Config $configHelper,
        array $data = []
    ) {
        parent::__construct($data);
        $this->layoutFactory = $layoutFactory;
        $this->layoutBuilderFactory = $layoutBuilderFactory;
        $this->pageConfig = $context->getPageConfig();
        $this->pageLayoutReader = $pageLayoutReader;
        $this->configHelper = $configHelper;
        $this->request = $context->getRequest();
        $this->contextLayout = $context->getLayout();
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {
        $return  = [];

        // foreach ($return as $key => &$block) {
        //     $block .= '<script type="text/javascript">console.log("'
        //         . $key . ' ' . md5($block)
        //         . '");</script>';
        // }
        $this->flushLayouts();

        return $return;
    }

    /**
     * @param array|string $handles
     * @return $this
     */
    public function setHandles($handles)
    {
        if (is_string($handles)) {
            $handles = [$handles];
        }
        $this->handles = $handles;
        return $this;
    }

    /**
     * @return string
     */
    private function getLayoutKey()
    {
        return implode(':', $this->handles);
    }

    /**
     * @param string $blockName
     * @return string
     */
    private function getBlockKey($blockName)
    {
        return $this->getLayoutKey() . ':' . (string) $blockName;
    }

    /**
     * @param $blockName
     * @param $handles
     * @return bool|\Magento\Framework\View\Element\AbstractBlock
     */
    protected function getBlockInstance($blockName)
    {
        return $this->getBlock($blockName);
    }

    /**
     * Retrieve block html
     *
     * @codeCoverageIgnore
     * @param string $blockName
     * @return string
     * @throws \Exception
     */
    public function getBlockHtml($blockName)
    {
        $block = $this->getBlock($blockName);

        if ($block) {
            $html = $block->toHtml();
        } else {
            $layout = $this->getCustomLayoutByHandles();
            $html = $layout->renderNonCachedElement($blockName);
        }

        $isDebugEnabled = false;
        $html = (!empty($html) ? $html : ($isDebugEnabled ? (' block not exist \'' . $blockName . '\' ' . time()
            . "\n xml <!-- " . $layout->getUpdate()->asString() . " -->"
            . "\n output <!--  " . $layout->getOutput() . " -->"
            . "\n handles <!--  " . implode(", ", $layout->getUpdate()->getHandles()) . " -->"
            ) : ''))
        ;
        // $layout->__destruct();

        return $html;
    }

    /**
     * @param $blockName
     * @return bool|\Magento\Framework\View\Element\AbstractBlock
     */
    private function getBlock($blockName)
    {
        $key = $this->getBlockKey($blockName);

        if (!isset($this->blocks[$key])) {
            $layout = $this->getCustomLayoutByHandles();
            $block = $layout->getBlock($blockName);
            $this->blocks[$key] = $block;
        }

        return $this->blocks[$key];
    }

    /**
     *
     * @return \Magento\Framework\View\Layout
     */
    private function getCustomLayoutByHandles()
    {
        $handles = $this->handles;
        $key = $this->getLayoutKey();
        if (!isset($this->layouts[$key])) {
            $fullActionName = end($handles);

            $layout = $this->layoutFactory->create();
            /** @var \Swissup\Ajaxpro\Model\View\Layout\Builder $builder */
            $builder = $this->getLayoutBuilder($layout);

            $builder
                ->setCustomHandles($handles)
                ->setFullActionName($fullActionName);
            $layout->setBuilder($builder);

            $this->prepareContextLayout();
            // $layout->publicBuild();

            $this->layouts[$key] = $layout;
        }

        return $this->layouts[$key];
    }

    /**
     *
     * @param \Magento\Framework\View\Layout $layout
     * @return \Magento\Framework\View\Layout\Builder
     */
    private function getLayoutBuilder($layout)
    {
        $builder = $this->layoutBuilderFactory->create(
            \Swissup\Ajaxpro\Model\View\Layout\Builder::TYPE_PAGE,
            [
                'layout' => $layout,
                'pageConfig' => $this->pageConfig,
                'pageLayoutReader' => $this->pageLayoutReader
            ]
        );

        return $builder;
    }

    /**
     * Add because context layout used in some 'custom' block
     * Magento\Catalog\Block\Product\View\Options\AbstractOptions
     * and Magento\Framework\Pricing\Render
     * Fix #22 circular dependency
     *
     * @return void
     */
    private function prepareContextLayout()
    {
        $update = $this->contextLayout->getUpdate();
        foreach ($update->getHandles() as $handle) {
            $update->removeHandle($handle);
        }
        foreach ($this->handles as $handle) {
            $update->addHandle($handle);
        }

        $this->contextLayout->isCacheable();
    }

    /**
     * @return $this
     */
    protected function flushLayouts()
    {
        /** @var \Magento\Framework\View\Layout $layout */
        foreach ($this->layouts as $layout) {
            $layout->__destruct();
        }
        return $this;
    }

    /**
     *
     * @param array $parameters page parameters
     * @param string|null $defaultHandle
     * @return array
     */
    public function generatePageLayoutHandles(array $parameters = [], $defaultHandle = null)
    {
        $pageHandles = [];
        $handle = $defaultHandle ? $defaultHandle : $this->getDefaultLayoutHandle();
        if (!empty($handle)) {
            $pageHandles[] = $handle;
        }

        foreach ($parameters as $key => $value) {
            $pageHandles[] = $handle . '_' . $key . '_' . $value;
        }
        return $pageHandles;
    }

    /**
     * @return string
     */
    public function getDefaultLayoutHandle()
    {
        /** @var \Magento\Framework\App\Request\Http $request */
        $request = $this->request;
        return strtolower($request->getFullActionName());
    }

    /**
     *
     * @return \Magento\Framework\App\RequestInterface
     */
    protected function getRequest()
    {
        return $this->request;
    }

    /**
     *
     * @return array
     */
    protected function getSectionNames()
    {
        $sectionNames = $this->getRequest()->getParam('sections');
        $sectionNames = $sectionNames ? array_unique(\explode(',', $sectionNames)) : [];

        return $sectionNames;
    }
}

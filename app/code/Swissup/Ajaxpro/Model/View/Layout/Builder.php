<?php
namespace Swissup\Ajaxpro\Model\View\Layout;

use Magento\Framework\App;
use Magento\Framework\Event;
use Magento\Framework\Profiler;
use Magento\Framework\View;

class Builder extends \Magento\Framework\View\Page\Builder
{
    const TYPE_PAGE = 'ajaxpro_page';

    /**
     *
     * @var string
     */
    protected $fullActionName;

    /**
     *
     * @var array
     */
    protected $customHandles = [];

    /**
     *
     * @param string $handle
     */
    public function setFullActionName($handle)
    {
        $this->fullActionName = (string) $handle;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getFullActionName()
    {
        if (empty($this->fullActionName)) {
            $this->fullActionName = $this->request->getFullActionName();
        }
        return $this->fullActionName;
    }

    /**
     *
     * @param array $handles
     * @return $this
     */
    public function setCustomHandles($handles)
    {
        $this->customHandles = $handles;
        return $this;
    }

    /**
     *
     * @return array
     */
    public function getCustomHandles()
    {
        return $this->customHandles;
    }

    // /**
    //  * @param boolean $status
    //  * @return $this
    //  */
    // public function setIsBuilt($status = true)
    // {
    //     $this->isBuilt = $status;
    //     return $this;
    // }

    /**
     * Load layout updates
     *
     * @return $this
     */
    protected function loadLayoutUpdates()
    {
        Profiler::start('LAYOUT');
        /* dispatch event for adding handles to layout update */
        $this->eventManager->dispatch(
            'layout_load_before',
            ['full_action_name' => $this->getFullActionName(), 'layout' => $this->layout]
        );
        Profiler::start('layout_load');

        /* load layout updates by specified handles */
        $this->layout->getUpdate()->load($this->customHandles);

        Profiler::stop('layout_load');
        Profiler::stop('LAYOUT');
        return $this;
    }

    /**
     * Generate layout blocks
     *
     * @return $this
     */
    protected function generateLayoutBlocks()
    {
        $this->readPageLayout();

        $this->beforeGenerateBlock();

        Profiler::start('LAYOUT');
        /* dispatch event for adding xml layout elements */
        $this->eventManager->dispatch(
            'layout_generate_blocks_before',
            ['full_action_name' => $this->getFullActionName(), 'layout' => $this->layout]
        );
        Profiler::start('layout_generate_blocks');

        /* generate blocks from xml layout */
        $this->layout->generateElements();

        Profiler::stop('layout_generate_blocks');
        $this->eventManager->dispatch(
            'layout_generate_blocks_after',
            ['full_action_name' => $this->getFullActionName(), 'layout' => $this->layout]
        );
        Profiler::stop('LAYOUT');

        $this->afterGenerateBlock();

        return $this;
    }
}

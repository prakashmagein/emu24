<?php

namespace Swissup\Easybanner\Plugin\Model;

class LayoutUpdate
{
    /**
     * @var \Swissup\Easybanner\Model\Layout\Builder
     */
    private $layoutBuilder;

    /**
     * @param \Swissup\Easybanner\Model\Layout\Builder $layoutBuilder
     */
    public function __construct(
        \Swissup\Easybanner\Model\Layout\Builder $layoutBuilder
    ) {
        $this->layoutBuilder = $layoutBuilder;
    }

    /**
     * Generate layout updates with placeholders
     *
     * @param \Magento\Framework\View\Model\Layout\Merge $subject
     * @param string $result
     * @param string $handle
     */
    public function afterGetDbUpdateString(
        \Magento\Framework\View\Model\Layout\Merge $subject,
        $result,
        $handle
    ) {
        $result = (string) $result;

        if ($handle === 'default') {
            $result .= $this->layoutBuilder->generateLayoutUpdate();
        }

        return $result;
    }
}

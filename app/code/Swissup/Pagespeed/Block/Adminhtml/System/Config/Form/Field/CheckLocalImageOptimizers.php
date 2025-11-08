<?php

namespace Swissup\Pagespeed\Block\Adminhtml\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;

class CheckLocalImageOptimizers extends Field
{
    /**
     * @var \Swissup\ImageOptimizer\Model\CheckImageOptimizerExisting
     */
    private $checker;

    /**
     * GettingStarted constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Swissup\ImageOptimizer\Model\CheckImageOptimizerExisting $checker
     * @param mixed[] $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Swissup\ImageOptimizer\Model\CheckImageOptimizerExisting $checker,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->checker = $checker;
    }

    /**
     * Retrieve element HTML markup
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $isAllExecutable = $this->checker
            ->setForceLocal(true)
            ->isAllExecutable();
        $note = '';
        if (!$isAllExecutable) {
            $messages = $this->checker->getMessages();
            foreach($messages as $message) {
                $note .= '<span>' . $message . '</span><br/>';
            }
        }
        return empty($note) ? '' : '<p class="message message-error">' . $note . '</p>';
    }
}

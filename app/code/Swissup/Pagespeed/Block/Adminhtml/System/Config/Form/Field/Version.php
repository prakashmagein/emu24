<?php

namespace Swissup\Pagespeed\Block\Adminhtml\System\Config\Form\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Version extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     *
     * @var \Swissup\Core\Model\ComponentList\Loader
     */
    private $loader;

    /**
     * GettingStarted constructor.
     *
     * @param Context $context
     * @param mixed[] $data
     */
    public function __construct(
        Context $context,
        \Swissup\Core\Model\ComponentList\Loader $loader,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->loader = $loader;
    }

    /**
     * @param string $version
     * @return string
     */
    private function getMinorVersion($version)
    {
        $parts = explode('.', $version);
        if (count($parts) >= 2) {
            return $parts[0] . '.' . $parts[1] . '.0';
        }
        return $version;
    }

    /**
     * Retrieve element HTML markup
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $moduleCode = 'Swissup_Pagespeed';
        $items = $this->loader->getItems();
        if (!isset($items[$moduleCode])) {
            return '';
        }
        $item = $items[$moduleCode];
        $version = $item['version'] ?? '';
        $latestVersion = $item['latest_version'] ?? '';

        $isOutdated = version_compare($version, $latestVersion, '<');
        $isVeryOutdated = version_compare($version, $this->getMinorVersion($latestVersion), '<');
        $messageCssClass = 'message-' . ( $isOutdated ? ($isVeryOutdated ? 'error' : 'note') : 'success');
        $message = __(($isOutdated ? '%1 is outdated' : '%1 is up to date '), $version);
        if ($isOutdated) {
            $message .= ' (' . __('latest version is %1', $latestVersion) . ')';
        }
        return '<p class="message ' . $messageCssClass . '"><span>' .
            $message.
        '</span></p>';
    }
}

<?php

namespace Swissup\Pagespeed\Plugin\View\Asset\NotationResolver;

class VariablePlugin
{
    /**
     * @param \Magento\Framework\View\Asset\NotationResolver\Variable $subject
     * @param string $result
     * @return string
     */
    public function afterGetPlaceholderValue(
        \Magento\Framework\View\Asset\NotationResolver\Variable $subject,
        $result,
        $placeholder = null
    ) {
        if ($result || !$placeholder) {
            return $result;
        }

        // Fix "Call to a member function getPackage() on null" when theme
        // references to module resources from critical css (FontAwesome)
        if (strpos((string) $placeholder, 'pagespeed_') !== false) {
            return '{' . $placeholder . '}';
        }

        return $result;
    }
}

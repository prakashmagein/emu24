<?php
namespace Swissup\Amp\Helper;

class Libxml extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Check if libxml version is suitable for AMP html markup
     * Versions prior to 2.8 does not support <noscript> tag inside head.
     *
     * @return boolean
     */
    public function isLibxmlVersionSupported()
    {
        if (defined('LIBXML_DOTTED_VERSION')) {
            return version_compare(LIBXML_DOTTED_VERSION, '2.8.0', '>=');
        }

        return true;
    }
}

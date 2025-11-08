<?php

namespace Swissup\Attributepages\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    public function isDirectLinkAllowed()
    {
        return $this->scopeConfig->isSetFlag('attributepages/seo/allow_direct_option_link');
    }

    public function getUrlSuffix()
    {
        return $this->scopeConfig->getValue('attributepages/seo/url_suffix');
    }

    public function getSuffixIndex($string)
    {
        $suffix = $this->getUrlSuffix();

        if ($suffix) {
            $index = strrpos($string, $suffix);
            if ($index > 0 && $index + strlen($suffix) === strlen($string)) {
                return $index;
            }
        }

        return false;
    }
}

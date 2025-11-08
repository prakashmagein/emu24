<?php
namespace Swissup\Amp\Model\Html\Filter\Str;

class Libxml
{
    /**
     * @var \Swissup\Amp\Helper\Libxml
     */
    protected $libxmlHelper;

    /**
     * @param \Swissup\Amp\Helper\Libxml $libxmlHelper
     */
    public function __construct(
        \Swissup\Amp\Helper\Libxml $libxmlHelper
    ) {
        $this->libxmlHelper = $libxmlHelper;
    }

    /**
     * libxml < 2.8 fix:
     *     Move <noscript> tag back into head section.
     *
     * @param  string $html
     * @return string
     */
    public function process($html)
    {
        if ($this->libxmlHelper->isLibxmlVersionSupported()) {
            return $html;
        }

        $find = '<noscript><style amp-boilerplate>body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}</style></noscript>';
        $result = str_replace($find, '', $html);

        $find = '</head><body';
        $replace = '<noscript><style amp-boilerplate>body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}</style></noscript></head><body';

        return str_replace($find, $replace, $result);
    }
}

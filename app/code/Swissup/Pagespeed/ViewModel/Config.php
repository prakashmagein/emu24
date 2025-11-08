<?php

namespace Swissup\Pagespeed\ViewModel;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Config extends DataObject implements ArgumentInterface
{
    /**
     *
     * @var \Swissup\Pagespeed\Helper\Config
     */
    private $configHelper;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @param \Swissup\Pagespeed\Helper\Config $configHelper
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        \Swissup\Pagespeed\Helper\Config $configHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        array $data = []
    ) {
        parent::__construct($data);
        $this->configHelper = $configHelper;
        $this->jsonEncoder = $jsonEncoder;
    }

    /**
     *
     * @return string
     */
    public function getJsLibsInit()
    {
        $libs = [];
        if ($this->configHelper->isCriticalCssEnable()) {
            $libs[] = 'cssrelpreload';
        }

        foreach ($libs as &$lib) {
            $lib = '"Swissup_Pagespeed/js/lib/' . $lib . '":{}';
        }

        return implode(',', $libs);
    }
}

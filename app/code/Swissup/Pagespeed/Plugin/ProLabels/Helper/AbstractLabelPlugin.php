<?php

namespace Swissup\Pagespeed\Plugin\ProLabels\Helper;

use Swissup\Pagespeed\Model\Minifier\HtmlFactory as MinifyHTMLFactory;

class AbstractLabelPlugin
{
    /**
     * @var \Swissup\Pagespeed\Helper\Config
     */
    private $helper;

    /**
     *
     * @var \Swissup\Pagespeed\Model\Minifier\HtmlFactory
     */
    private $minifierFactory;

    /**
     * @param \Swissup\Pagespeed\Helper\Config $helper
     */
    public function __construct(
        \Swissup\Pagespeed\Helper\Config $helper,
        MinifyHTMLFactory $minifierFactory
    ) {
        $this->helper = $helper;
        $this->minifierFactory = $minifierFactory;
    }

    /**
     * Fix for Minify_HTML line 147
     *
     * @param \Swissup\ProLabels\Helper\AbstractLabel $subject
     * @param \Magento\Framework\DataObject $result
     * @return \Magento\Framework\DataObject
     */
    public function afterGetLabelOutputObject(
        \Swissup\ProLabels\Helper\AbstractLabel $subject,
        $result
    ) {
        $isEnable = $this->helper->isContentMinifyEnable();
        if ($isEnable) {
            $text = $result->getText();
            if (!empty($text)) {
                // use newlines before 1st attribute in open tags (to limit line lengths)
                // $text = preg_replace('/(<[a-z\\-]+)\\s+([^>]+>)/iu', "$1\n$2", $text);
                /** @var \Swissup\Pagespeed\Model\Minifier\Html $minifier */
                $minifier = $this->minifierFactory->create([
                    'html' => $text
                ]);

                try {
                    $text = $minifier->process();
                } catch (\Exception $e) {
                    throw $e;
                }
            }
            $result->setText($text);
        }

        return $result;
    }
}

<?php

namespace Swissup\Pagespeed\Plugin\Framework\App;

use Swissup\Pagespeed\Model\Minifier\HtmlFactory as MinifyHTMLFactory;

class CachePlugin
{
    /**
     * @var \Swissup\Pagespeed\Helper\Config
     */
    private $config;

    /**
     *
     * @var \Swissup\Pagespeed\Model\Minifier\HtmlFactory
     */
    private $minifierFactory;

    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;

    /**
     * @param \Swissup\Pagespeed\Helper\Config $config
     * @param MinifyHTMLFactory $minifierFactory
     * @param \Magento\Framework\App\State $appState
     */
    public function __construct(
        \Swissup\Pagespeed\Helper\Config $config,
        MinifyHTMLFactory $minifierFactory,
        \Magento\Framework\App\State $appState
    ) {
        $this->config = $config;
        $this->minifierFactory = $minifierFactory;
        $this->appState = $appState;
    }

    /**
     *
     * @param \Magento\Framework\App\CacheInterface $subject
     * @param string $data
     * @param string $identifier
     * @param array $tags
     * @param int $lifeTime
     * @return array
     */
    public function beforeSave(
        \Magento\Framework\App\CacheInterface $subject,
        $data,
        $identifier,
        $tags = [],
        $lifeTime = null
    ) {
        \Magento\Framework\Profiler::start(__METHOD__);
        if ($this->config->isContentMinifyEnable() &&
            $this->appState->getAreaCode() === \Magento\Framework\App\Area::AREA_FRONTEND
        ) {
            $blockHtmlTag = \Magento\Framework\App\Cache\Type\Block::TYPE_IDENTIFIER;
            if (!empty($data) && in_array($blockHtmlTag, $tags)) {
                $data = $this->minify($data);
            }
        }
        \Magento\Framework\Profiler::stop(__METHOD__);

        return [$data, $identifier, $tags, $lifeTime];
    }

    /**
     *
     * @param  string $html
     * @return string
     */
    private function minify($html)
    {
        $options = [];
        $minifier = $this->minifierFactory->create([
            'html' => $html,
            'options' => $options
        ]);

        try {
            $html = $minifier->process();
        } catch (\Exception $e) {
            throw $e;
        }

        return $html;
    }
}

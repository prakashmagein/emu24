<?php

namespace Swissup\Pagespeed\Plugin\View\Page\Config;

class RendererPlugin
{
    private $fixIntegrityPlugin;

    public function __construct(
        \Swissup\Pagespeed\Plugin\View\Asset\GroupedCollectionPlugin $fixAddDefaultPropertiesToGroupPlugin
    ) {
        $this->fixIntegrityPlugin = $fixAddDefaultPropertiesToGroupPlugin;
    }

    /**
     * @param \Magento\Framework\View\Page\Config\Renderer $subject
     * @param $result
     * @return string
     */
    public function afterRenderAssets(
        \Magento\Framework\View\Page\Config\Renderer $subject,
        $result
    ) {
        $integrities = $this->fixIntegrityPlugin->getIntegrities();
        if (empty($integrities)) {
            return $result;
        }
        $regExpForScripts = '/<script\b[^>]*>.*?<\/script>/is';
        $scriptMatches = $replacements = [];
        preg_match_all($regExpForScripts, (string) $result, $scriptMatches);
        if (!isset($scriptMatches[0])) {
            return $result;
        }
        foreach ($scriptMatches[0] as $script) {
            foreach ($integrities as $path => $integrity) {
                if (str_contains($script, $path) && str_contains($script, ' src=')) {
                    $replacements[$script] =  str_replace(
                        ' src=',
                        ' integrity="' . $integrity . '" crossorigin="anonymous" src=',
                        $script
                    );
                }
            }
        }
        $result = strtr($result, $replacements);
        return $result;
    }
}

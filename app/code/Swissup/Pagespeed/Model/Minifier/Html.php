<?php
namespace Swissup\Pagespeed\Model\Minifier;

// @phpstan-ignore-next-line
class Html extends \Minify_HTML
{
    /**
     *
     * @param  array $m
     * @return string
     */
    protected function _commentCB($m)
    {
        return (0 === strpos($m[1], '[')
            || false !== strpos($m[1], '<![')
            // || false !== strpos($m[1], 'ajaxpro_')
            || false !== stripos($m[1], 'esi <')
            || false !== stripos($m[1], ' fpc')
            || false !== stripos($m[1], ' ko ')
            || false !== stripos($m[1], ' /ko ')
            )
            ? $m[0]
            : '';
    }

    protected function _removeStyleCB($m)
    {
        $openStyle = "<style{$m[1]}";
        $css = (string)  $m[2];
        // remove HTML comments
        $css = preg_replace('/(?:^\\s*<!--|-->\\s*$)/u', '', $css);

        // remove CDATA section markers
        $css = $this->_removeCdata($css);/** @phpstan-ignore-line */

        // minify
        $minifier = $this->_cssMinifier? $this->_cssMinifier : 'trim';/** @phpstan-ignore-line */
        try {
        $css = call_user_func($minifier, (string)    $css);
        } catch (\Exception $e) {
        }

                    /* @phpstan-ignore-next-line */
        return (string) $this->_reservePlace(
            $this->_needsCdata($css)/** @phpstan-ignore-line */
            ? "{$openStyle}/*<![CDATA[*/{$css}/*]]>*/</style>"
            : "{$openStyle}{$css}</style>"
        );
    }

    /**
     *
     * @param  array $m
     * @return string
     */
    protected function _removeScriptCB($m)
    {
        $openScript = "<script{$m[2]}";
        $js = (string) $m[3];

        // whitespace surrounding? preserve at least one space
        $ws1 = ($m[1] === '') ? '' : ' ';
        $ws2 = ($m[4] === '') ? '' : ' ';

        // remove HTML comments (and ending "//" if present)
        // if ($this->_jsCleanComments) {
        //     $js = preg_replace('/(?:^\\s*<!--\\s*|\\s*(?:\\/\\/)?\\s*-->\\s*$)/u', '', $js);
        // }

        // remove CDATA section markers
        $js = $this->_removeCdata($js);/** @phpstan-ignore-line */

        // minify
        $minifier = $this->_jsMinifier ? $this->_jsMinifier : 'trim';/** @phpstan-ignore-line */

        $exceptionTypes = [
            'application/ld+json',
            'text/x-custom-template',
            'x-magento-template',
            'data-breeze',
            // 'x-magento-init'
        ];
        foreach ($exceptionTypes as $type) {
            if (false !== stripos($m[2], $type)) {
                $minifier = 'trim';
            }
        }
        try {
            $js = call_user_func($minifier, (string) $js);
        } catch (\Exception $e) {
            // var_dump($e->getMessage());
            // var_dump($e->getTraceAsString());
            // var_dump($js);
            // var_dump($m);
            // die;
            // throw $e;
        }

        /* @phpstan-ignore-next-line */
        return (string) $this->_reservePlace(
            $this->_needsCdata($js) /** @phpstan-ignore-line */
            ? "{$ws1}{$openScript}/*<![CDATA[*/{$js}/*]]>*/</script>{$ws2}"
            : "{$ws1}{$openScript}{$js}</script>{$ws2}"
        );
    }
}

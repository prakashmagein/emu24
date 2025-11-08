<?php
namespace Swissup\Pagespeed\Model\Optimizer;

use Swissup\Pagespeed\Helper\Config;
use Magento\Framework\App\Response\Http as ResponseHttp;
use Magento\Framework\Controller\ResultInterface;

abstract class AbstractOptimizer implements OptimizerInterface
{
    /**
     *
     * @var \DOMDocument
     */
    private $dom;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Perform result postprocessing
     *
     * @param ResponseHttp $response
     * @return ResponseHttp
     */
    public function process(?ResponseHttp $response = null)
    {
        return $response;
    }

    /**
     * @inheritdoc
     */
    public function isApplicable(ResultInterface $result): bool
    {
        return true;
    }

    /**
     *
     * @param  string $htmlInput
     * @return \DOMDocument
     */
    protected function getDomDocument($htmlInput)
    {
        $html = (string) $htmlInput;
        $html = trim($html);
        if ($this->dom !== null) {
            return $this->dom;
        }
        // Fix for \DOMDocument->saveHTML
        // escape too early close tag inner script : <script>alert("</div>")</script>
        // https://stackoverflow.com/questions/236073/why-split-the-script-tag-when-writing-it-with-document-write/236106#236106
        $regExp = '/<script\b[^>]*>(.*?)<\/script>/is';
        $matches = [];
        preg_match_all($regExp, $html, $matches);

        foreach ($matches[1] as $_script) {
            if (strstr($_script, '</')) {
                $html = str_replace($_script, str_replace('</', '<\/', $_script), $html);
            }
        }

        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        // silence warnings and erros during parsing.
        // More at http://php.net/manual/en/function.libxml-use-internal-errors.php
        $oldUseErrors = libxml_use_internal_errors(true);
        $html = $this->getHtmlToUtf8($html);

        $dom->encoding = 'UTF-8';
        $definedOptions = [];
//        if (defined('LIBXML_HTML_NOIMPLIED')) {
//            $definedOptions[] = LIBXML_HTML_NOIMPLIED;
//        }
        if (defined('LIBXML_HTML_NODEFDTD')) {
            $definedOptions[] = LIBXML_HTML_NODEFDTD;
        }
        if (defined('LIBXML_SCHEMA_CREATE')) {
            $definedOptions[] = LIBXML_SCHEMA_CREATE;
        }
        $loadOptions = 0;
        foreach ($definedOptions as $definedOption) {
            $loadOptions = $loadOptions | $definedOption;
        }
        if ($html !== '') {
            $dom->loadHTML($html, $loadOptions);
        }

        // restore old value
        libxml_use_internal_errors($oldUseErrors);

        $this->dom = $dom;
        return $dom;
    }

    /**
     *
     * @param  string $html
     * @return string
     */
    protected function getHtmlToUtf8($html)
    {
        $html = htmlentities($html);
        $html = htmlspecialchars_decode($html);

        return $html;
    }

    /**
     *
     * @param  string $html
     * @return string
     */
    protected function getUtf8ToHtml($html)
    {
        $html = htmlentities($html, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, "UTF-8");

        return $html;
    }

    /**
     *
     * @param  string $html
     * @return \DOMXPath
     */
    protected function getDOMXPath($html)
    {
        $dom = $this->getDomDocument($html);
        $xpath = new \DOMXPath($dom);

        return $xpath;
    }

    /**
     * @deprecated 1.2.1
     * @param \DOMDocument $document
     * @return string
     */
    protected function getSaveHTML($document)
    {
        $html = (string) $document->saveHTML();

        $regExp = '/<script(\b[^>]*)>(.*?)<\/script>/is';
        $matches = [];
        preg_match_all($regExp, $html, $matches);

        foreach ($matches[2] as $i => $_script) {
            if (!isset($matches[1][$i])) {
                continue;
            }
            $attrs = $matches[1][$i];
            if ((strstr($attrs, 'x-magento') || strstr($attrs, 'text/x-custom-template'))
                && strstr($_script, '<\/')
                && strstr($_script, '<\/script>') === false
            ) {
                $html = str_replace($_script, str_replace('<\/', '</', $_script), $html);
            }
        }

        return $html;
    }
}

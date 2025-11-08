<?php
namespace Swissup\Pagespeed\Model\Optimizer\Preload;

use Swissup\Pagespeed\Helper\Config;
use Magento\Framework\App\Response\Http as ResponseHttp;
use Swissup\Pagespeed\Model\Optimizer\AbstractOptimizer;

class Preconnect extends AbstractOptimizer
{
    /**
     * @var \Swissup\Pagespeed\Model\ExtaractHosts
     */
    private $extractHosts;

    /**
     * @param Config $config
     * @param \Swissup\Pagespeed\Model\ExtaractHosts $extaractHosts
     */
    public function __construct(Config $config, \Swissup\Pagespeed\Model\ExtaractHosts $extaractHosts)
    {
        parent::__construct($config);
        $this->extractHosts = $extaractHosts;
    }

    /**
     * Perform result postprocessing
     *
     * @param ResponseHttp $response
     * @return ResponseHttp
     */
    public function process(?ResponseHttp $response = null)
    {
        if (!$this->config->isPreconnectEnable() || $response === null) {
            return $response;
        }

        $html = $response->getBody();
        if (empty($html) || strpos($html, '<html') === false) {
            return $response;
        }
        $xpath = $this->getDOMXPath($html);
        $urls = $this->extractHosts->process($xpath);

        if (!empty($urls)) {
            $_html = '';
            // $_html = '<meta http-equiv="x-dns-prefetch-control" content="on">' . "\n";
            foreach (array_keys($urls) as $url) {
                $_html .= "<link rel=\"preconnect\" href=\"{$url}\" crossorigin=\"anonymous\">\n";
            }

            $needle = '</title>';
            $pos = strpos($html, $needle);
            if ($pos !== false) {
                $html = substr_replace($html, $needle . $_html, $pos, strlen($needle));
                $response->setBody($html);
            }
        }

        return $response;
    }
}

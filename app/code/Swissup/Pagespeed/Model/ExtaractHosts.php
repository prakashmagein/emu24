<?php
namespace Swissup\Pagespeed\Model;

class ExtaractHosts
{
    /**
     * @var array
     */
    private $hosts = [];

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(\Magento\Store\Model\StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    /**
     * @param \DOMXPath $xpath
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function process($xpath)
    {
        //$xpath = $this->getDOMXPath($html);
        $hashId = false;
        if (function_exists('spl_object_hash')) {
            $hashId = spl_object_hash($xpath);
            if (isset($this->hosts[$hashId])) {
                return $this->hosts[$hashId];
            }
        }

        $selectors = [
            '//link[not(@rel="alternate")]' => 'href',
            '//script' => 'src',
            '//img'    => 'src',
        ];
        $urls = [];
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();
        $baseUrlHost = (string) $this->getHost($baseUrl);

        foreach ($selectors as $xpathString => $attribute) {
            $nodes = $xpath->query($xpathString);
            foreach ($nodes as $node) {
                $url = $node->getAttribute($attribute);
                $_url = (string) $this->getHost($url);
                if (!empty($_url) && false === strpos($url, $baseUrlHost)) {
                    $urls['//' . $_url] = $url;
                }
            }
        }
        if ($hashId !== false) {
            $this->hosts[$hashId] = $urls;
        }

        return $urls;
    }

    /**
     * insteadof parse_url($url, PHP_URL_HOST)
     *
     * @param string $url
     * @return mixed
     */
    private function getHost($url)
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
             return;
        }
        $schemes = ['http', 'https' /*, 'mailto', 'file', 'urn', 'tag'*/];
        $schemes = implode('|', $schemes);

        if (!preg_match("@^(" . $schemes . ")?://@", $url)) {
            return;
        }

        $uri = \Laminas\Uri\UriFactory::factory($url);
        return $uri->getHost();
    }
}

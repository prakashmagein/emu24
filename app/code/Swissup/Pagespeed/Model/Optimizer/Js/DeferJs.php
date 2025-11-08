<?php
namespace Swissup\Pagespeed\Model\Optimizer\Js;

use Swissup\Pagespeed\Helper\Config;
use Magento\Framework\App\Response\Http as ResponseHttp;
use Swissup\Pagespeed\Model\Optimizer\AbstractOptimizer;
use Magento\Store\Model\StoreManagerInterface;

class DeferJs extends AbstractOptimizer
{
    private \Swissup\Pagespeed\Model\Preload $preloader;
    private StoreManagerInterface $storeManager;
    private ?string $baseUrlHost = null;
    private array $preload = [];
    private ?array $ignoreSignatures = null;

    public function __construct(
        Config $config,
        \Swissup\Pagespeed\Model\Preload $preloader,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($config);
        $this->preloader = $preloader;
        $this->storeManager = $storeManager;
    }

    private function getIgnoreWithAttributes(): array
    {
        return [
            'data-defer-js-ignore' => null,
            'po_cmp_ignore' => null,
        ];
    }

    private function hasIgnoreAttribute(\DOMElement $node): bool
    {
        foreach ($this->getIgnoreWithAttributes() as $attribute => $value) {
            if ($node->hasAttribute($attribute) && ($value === null || $node->getAttribute($attribute) === $value)) {
                return true;
            }
        }
        return false;
    }

    private function getIgnoreSignatures(): array
    {
        if ($this->ignoreSignatures !== null) {
            return $this->ignoreSignatures;
        }

        $signatures = array_merge(
            $this->config->getDeferJsIgnores(),
            [
                'type="text/x-magento-template"',
                'window.checkout = ',
                'window.cookiesConfig = ',
                'window.checkoutConfig = ',
                'window.sriHashes = ',
                'Array = document.querySelectorAll(',
                "document.addEventListener('DOMContentLoaded'",
                'customerDataConfig = ',
                'sectionsConfig = ',
                'googleMapsConfig = ',
                'window.required = ',
            ]
        );

        foreach ($signatures as $signature) {
            $signatures[] = str_replace([' = ', '= ', ' ='], '=', $signature);
        }

        foreach ($this->getIgnoreWithAttributes() as $attribute => $value) {
            $signatures[] = ' ' . $attribute . ($value !== null ? '="' . $value . '"' : '');
        }

        $this->ignoreSignatures = array_unique(array_filter($signatures));
        return $this->ignoreSignatures;
    }

    private function hasIgnoreSignature(string $scriptString): bool
    {
        foreach ($this->getIgnoreSignatures() as $signature) {
            if (strpos($scriptString, $signature) !== false) {
                return true;
            }
        }
        return false;
    }

    private function getBaseUrlHost(): ?string
    {
        if ($this->baseUrlHost === null) {
            $baseUrl = $this->storeManager->getStore()->getBaseUrl();
            $this->baseUrlHost = $this->getHost($baseUrl);
        }
        return $this->baseUrlHost;
    }

    private function getHost(string $url): ?string
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }
        $uri = \Laminas\Uri\UriFactory::factory($url);
        return $uri->getHost();
    }

    private function isThirdPartyScript(string $src): bool
    {
        if (!filter_var($src, FILTER_VALIDATE_URL)) {
            return false;
        }
        return $this->getHost($src) !== $this->getBaseUrlHost();
    }

    private function getDomElementHtml(\DOMElement $element): string
    {
        $document = new \DOMDocument();
        $document->appendChild($document->importNode($element, true));
        return $document->saveHTML();
    }

    private function fixTypicalJsErrors(string $html): string
    {
        return strtr($html, [
            "            window.isCustomerLoggedIn = window.checkoutConfig.isCustomerLoggedIn;
            require([" => "            window.isCustomerLoggedIn = window.checkoutConfig.isCustomerLoggedIn;</script>
                <script>require(["
        ]);
    }

    private function fixBackSlashes(string $script): string
    {
        if (strpos($script, '<\/') === false ||
            strpos($script, '<\/script>') !== false
        ) {
            return $script;
        }

        $templateTypes = ['text/x-magento', 'text/html', 'text/x-custom-template'];
        $isCustomTemplateType = false;

        foreach ($templateTypes as $templateType) {
            if (strpos($script, $templateType) !== false) {
                $isCustomTemplateType = true;
                break;
            }
        }

        if ($isCustomTemplateType) {
            $script = str_replace('<\/', '</', $script);
        }

        return $script;
    }

    private function getAllScripts(string $html): array
    {
        preg_match_all('/<script\b[^>]*>.*?<\/script>/is', $html, $matches);
        return $matches[0] ?? [];
    }

    private function addPagespeedHashes(string $html): string
    {
        return preg_replace_callback('/<script\b[^>]*>.*?<\/script>/is', function ($matches) {
            $scriptTag = $matches[0];
            $hash = hash('sha256', $scriptTag);
            return str_replace('<script', '<script data-pagespeed-hash="' . $hash . '"', $scriptTag);
        }, $html);
    }

    private function removeScriptsByPagespeedHashes(string $html, array $hashes): string
    {
        foreach ($hashes as $hash) {
            $signature = '<script data-pagespeed-hash="' . $hash . '"';
            $start = strpos($html, $signature);
            if ($start === false) continue;
            $end = strpos($html, '</script>', $start) + strlen('</script>');
            $html = substr_replace($html, '', $start, $end - $start);
        }
        return $html;
    }

    private function removePagespeedHashes(string $html): string
    {
        return preg_replace('/\sdata-pagespeed-hash="[^"]*?"/is', '', $html);
    }

    public function process(?ResponseHttp $response = null): ?ResponseHttp
    {
        if (!$this->config->isDeferJsEnable() || $response === null) {
            return $response;
        }

        $html = (string) $response->getBody();
        if (strpos($html, '</body>') === false) {
            return $response;
        }

        $html = $this->fixTypicalJsErrors($html);
        $html = $this->addPagespeedHashes($html);

        $allScripts = $this->getAllScripts($html);
        $dom = $this->getDomDocument(implode("\n", $allScripts));
        $xpath = new \DOMXPath($dom);

        $movedScripts = [];
        $jsCounter = 0;

        foreach ($xpath->query('//script') as $node) {
            $domElement = $node->cloneNode(true);

            if (!$this->hasIgnoreAttribute($domElement)) {
                $scriptHtml = $this->getDomElementHtml($domElement);
                if (!$this->hasIgnoreSignature($scriptHtml)) {
                    $domElement = $this->prepareElement($domElement, $jsCounter);
                    $hash = $domElement->getAttribute('data-pagespeed-hash');
                    $scriptHtml = $this->fixBackSlashes($this->getDomElementHtml($domElement));
                    $movedScripts[$hash] = trim($scriptHtml);
                    $jsCounter++;
                }
            }
            $node->parentNode->removeChild($node);
        }

        $html = $this->removeScriptsByPagespeedHashes($html, array_keys($movedScripts));
        $html = str_replace('</body>', implode("\n", $movedScripts) . '</body>', $html);
        $html = $this->removePagespeedHashes($html);

        $response->setBody($html);
        $this->preloader->add($this->preload, 'script', 'preload');
        return $response;
    }

    private function prepareElement(\DOMElement $node, int $jsCounter): \DOMElement
    {
        $isUnpackScript = $this->config->isDeferJsUnpackEnable();
        $delayScriptType = $this->config->getDelayScriptType();
        $offset = $this->config->isJsMergeEnable() ? 2 : 5;

        $src = $node->getAttribute('src');
        if (!empty($src)) {
            $this->preload[] = ['as' => 'script', 'href' => $src];
        }

        $type = $node->getAttribute('type');
        if (empty($type) || $type === 'application/javascript') {
            $node->setAttribute('type', 'text/javascript');
            $type = 'text/javascript';
        }

        if ($type === 'text/javascript') {
            if (empty($src) && $isUnpackScript && $jsCounter >= $offset) {
                $node->setAttribute('type', $delayScriptType);
            }
            if (!empty($src)) {
                if ($this->isThirdPartyScript($src) && $jsCounter > 1) {
                    $node->setAttribute('defer', 'defer');
                } elseif ($jsCounter > 5) {
                    $node->setAttribute('async', 'async');
                }
            }
            if ($node->hasAttribute('nonce')) {
                $nonce = $node->getAttribute('nonce');
                if (!empty($nonce)) {
                    $node->setAttribute('defered-nonce', $nonce);
                }
            }
        }
        return $node;
    }
}

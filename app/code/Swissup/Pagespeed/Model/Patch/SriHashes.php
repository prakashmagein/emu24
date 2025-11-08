<?php
namespace Swissup\Pagespeed\Model\Patch;

class SriHashes implements PatcherInterface
{
    private $response;

    private $replacePlaceholders = [];

    public function apply(?\Magento\Framework\App\Response\Http $response = null)
    {
        $this->response = $response;
        $html = (string) $this->response->getBody();

        if (empty($html)) {
            return;
        }
        $signtature = 'window.sriHashes = ';
        $hasSriHashes = strpos($html, $signtature) !== false;
        if ($hasSriHashes === false) {
            return;
        }

        $startPosition = strpos($html, $signtature);
        $endPosition = strpos($html, '<', $startPosition);

        $script = substr($html, $startPosition, $endPosition - $startPosition);
        $placeholder = $signtature . '["' . hash('sha256', $script) . 'placeholder"];';
        $placeholder = str_replace(["\n", "\t", " "], '', $placeholder);
        $this->replacePlaceholders[$placeholder] = $script;
        $html = str_replace($script, $placeholder, $html);

        $this->response->setBody($html);
    }

    public function restore()
    {
        $html = (string) $this->response->getBody();
        foreach ($this->replacePlaceholders as $placeholder => $replace) {
            $html = str_replace($placeholder, $replace, $html);
            $placeholder = str_replace(["\n", "\t", " "], "", $placeholder);
            $html = str_replace($placeholder, $replace, $html);
        }
        $this->response->setBody($html);
    }
}

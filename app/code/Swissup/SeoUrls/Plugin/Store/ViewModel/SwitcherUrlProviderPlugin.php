<?php

namespace Swissup\SeoUrls\Plugin\Store\ViewModel;

use Magento\Store\ViewModel\SwitcherUrlProvider as Subject;
use Magento\Framework\App\ActionInterface;

class SwitcherUrlProviderPlugin
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    private $encoder;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Url\EncoderInterface $encoder
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Url\EncoderInterface $encoder
    )
    {
        $this->request = $request;
        $this->urlBuilder = $urlBuilder;
        $this->storeManager = $storeManager;
        $this->encoder = $encoder;
    }

    /**
     * @param \Magento\Store\ViewModel\SwitcherUrlProvider $subject
     * @param callable $proceed
     * @param \Magento\Store\Model\Store $store
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundGetTargetStoreRedirectUrl(
        \Magento\Store\ViewModel\SwitcherUrlProvider $subject,
        callable $proceed,
        \Magento\Store\Model\Store $store
    ):string
    {
        $rewriteRequestPath = $this->request->getAlias(\Magento\Framework\UrlInterface::REWRITE_REQUEST_PATH_ALIAS);
        if (empty($rewriteRequestPath)) {
            return $proceed($store);
        }

        return $this->urlBuilder->getUrl(
            'stores/store/redirect',
            [
                '___store' => $store->getCode(),
                '___from_store' => $this->storeManager->getStore()->getCode(),
                ActionInterface::PARAM_NAME_URL_ENCODED => $this->encoder->encode(
                    $this->getCorrectCurrentUrl($store, false)
                ),
            ]
        );
    }

    /**
     *  clone of \Magento\Store\Model\Store::getCurrentUrl
     *
     * @param \Magento\Store\Model\Store $store
     * @param mixed $fromStore
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getCorrectCurrentUrl(\Magento\Store\Model\Store $store, $fromStore = true)
    {
        $requestString = $this->request->getAlias(\Magento\Framework\UrlInterface::REWRITE_REQUEST_PATH_ALIAS);
        $requestString = $requestString ?: $this->request->getRequestString();
        $requestString = $this->urlBuilder->escape(ltrim($requestString, '/'));


        $storeUrl = $store->getUrl('', ['_secure' => $this->storeManager->getStore()->isCurrentlySecure()]);

        if (!filter_var($storeUrl, FILTER_VALIDATE_URL)) {
            return $storeUrl;
        }

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $storeParsedUrl = parse_url($storeUrl);

        $storeParsedQuery = [];
        if (isset($storeParsedUrl['query'])) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            parse_str($storeParsedUrl['query'], $storeParsedQuery);
        }

        $currQuery = $this->request->getQueryValue();

        foreach ($currQuery as $key => $value) {
            $storeParsedQuery[$key] = $value;
        }

        if (!$store->isUseStoreInUrl()) {
            $storeParsedQuery['___store'] = $store->getCode();
        }

        if ($fromStore !== false) {
            $storeParsedQuery['___from_store'] = $fromStore ===
                true ? $this->storeManager->getStore()->getCode() : $fromStore;
        }

        $requestStringParts = explode('?', $requestString, 2);
        $requestStringPath = $requestStringParts[0];
        if (isset($requestStringParts[1])) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            parse_str($requestStringParts[1], $requestString);
        } else {
            $requestString = [];
        }

        $currentUrlQueryParams = array_merge($requestString, $storeParsedQuery);

        $currentUrl = $storeParsedUrl['scheme']
            . '://'
            . $storeParsedUrl['host']
            . (isset($storeParsedUrl['port']) ? ':' . $storeParsedUrl['port'] : '')
            . $storeParsedUrl['path']
            . $requestStringPath
            . ($currentUrlQueryParams ? '?' . http_build_query($currentUrlQueryParams) : '');

        return $currentUrl;
    }
}

<?php

namespace Swissup\Attributepages\Model;

class PageParamsExtractor
{
    private $request;

    private $attrpagesHelper;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Swissup\Attributepages\Helper\Data $attrpagesHelper
    ) {
        $this->request = $request;
        $this->attrpagesHelper = $attrpagesHelper;
    }

    public function extract()
    {
        $pathInfo = trim($this->request->getPathInfo(), '/');
        $pathParts = explode('/', $pathInfo);

        $identifiers = [];
        foreach ($pathParts as $i => $param) {
            $identifiers[] = urldecode($param);
            if ($i >= 1) {
                break;
            }
        }

        $key = array_key_last($identifiers);
        if ($index = $this->attrpagesHelper->getSuffixIndex($identifiers[$key])) {
            $identifiers[$key] = substr_replace($identifiers[$key], '', $index);
        }

        return $identifiers;
    }
}

<?php
namespace Swissup\Ajaxpro\Plugin\Helper\Url;

class AmastyXnotifDataPlugin
{
    /**
     *
     * @var \Swissup\Ajaxpro\Helper\Config
     */
    private $helper;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $httpRequest;

    /**
     * @var \Magento\Framework\Url\DecoderInterface
     */
    private $urlDecoder;

    /**
     * @param \Swissup\Ajaxpro\Helper\Config $helper
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
     */
    public function __construct(
        \Swissup\Ajaxpro\Helper\Config $helper,
        \Magento\Framework\App\RequestInterface $httpRequest,
        \Magento\Framework\Url\DecoderInterface $urlDecoder
    ) {
        $this->helper = $helper;
        $this->httpRequest = $httpRequest;
        $this->urlDecoder = $urlDecoder;
    }

    public function beforeGetEncodedUrl(
        \Amasty\Xnotif\Helper\Data $subject,
        $url = null
    ) {
        if (empty($url)){
            $request = $this->httpRequest;
            if ($request->isAjax()) {
                $ajaxproParam = $request->getParam('ajaxpro');
                if ($ajaxproParam) {
                    $paramName = \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED;
                    $uenc = $request->getParam($paramName);
                    if (!empty($uenc)) {
                        $url = $this->urlDecoder->decode($uenc);
                        return [$url];
                    }
                }
            }
        }
    }
}

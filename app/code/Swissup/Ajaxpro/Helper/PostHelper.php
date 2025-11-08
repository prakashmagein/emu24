<?php

namespace Swissup\Ajaxpro\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Url\Helper\Data as UrlHelper;

class PostHelper extends \Magento\Framework\Data\Helper\PostHelper
{
    /**
     * @var UrlHelper
     */
    private $urlHelper;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json $serializer
     */
    private $serializer;

    /**
     * @param Context $context
     * @param UrlHelper $urlHelper
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
     */
    public function __construct(
        Context $context,
        UrlHelper $urlHelper,
        \Magento\Framework\Serialize\Serializer\Json $serializer
    ) {
        parent::__construct($context, $urlHelper);
        $this->urlHelper = $urlHelper;
        $this->serializer = $serializer;
    }

    /**
     * get data for post by javascript in format acceptable to $.mage.dataPost widget
     *
     * @param string $url
     * @param array $data
     * @return string
     */
    public function getPostData($url, array $data = [])
    {
        $param = \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED;
        if (!isset($data[$param])) {
            $data[$param] = $this->urlHelper->getEncodedUrl();
            /** @var \Magento\Framework\App\Request\Http $request */
            $request = $this->_getRequest();
            if ($request->isAjax()) {
                $uenc = $this->_request->getParam($param);
                if (!empty($uenc)) {
                    $data[$param] = $uenc;
                }
            }
        }
        return $this->serializer->serialize(['action' => $url, 'data' => $data]);
    }
}

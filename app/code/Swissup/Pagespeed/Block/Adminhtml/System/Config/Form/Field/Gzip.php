<?php

namespace Swissup\Pagespeed\Block\Adminhtml\System\Config\Form\Field;

use Swissup\Pagespeed\Block\Adminhtml\System\Config\Form\Field\StoreAbstract as Field;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Gzip extends Field
{
    /**
     * @var \Magento\Framework\HTTP\Adapter\CurlFactory
     */
    private $curlFactory;

    /**
     * @var \Magento\Framework\HTTP\ResponseFactory
     */
    private $responseFactory;

    /**
     * GettingStarted constructor.
     *
     * @param Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory
     * @param \Magento\Framework\HTTP\ResponseFactory $responseFactory
     * @param mixed[] $data
     */
    public function __construct(
        Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory,
        \Magento\Framework\HTTP\ResponseFactory $responseFactory,
        array $data = []
    ) {
        parent::__construct($context, $storeManager, $data);
        $this->curlFactory = $curlFactory;
        $this->responseFactory = $responseFactory;
    }

    /**
     * Retrieve element HTML markup
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $url = $this->getStoreBaseUrl();
        $url = str_replace('http://', 'https://', $url);

        $note = '';
        $hasGZHandler = function_exists('ob_gzhandler') && ini_get('zlib.output_compression');
        $hasGzipModule = function_exists('apache_get_modules')
            && count(array_intersect(['mod_deflate', 'mod_gzip'], apache_get_modules())) > 0;
        if ($hasGZHandler || $hasGzipModule) {
            $message = 'GZIP is enabled';
            $cssClass = 'message-success';
        } else {
            $client = $this->curlFactory->create();
            $client->setOptions([
                CURLOPT_TIMEOUT => 20,
                CURLOPT_HEADER => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_NOBODY         => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_ENCODING => 'gzip',
            ]);
            try {
                $client->write(\Laminas\Http\Request::METHOD_GET, $url);
                // $responseBody = $client->read();
                $headers = [];
                if (!empty($responseBody)) {
                    $response = $this->responseFactory->create($responseBody);
                    $headers = $response->getHeaders();
                }
            } catch (\Exception $e) {
                $headers = [];
                throw $e;
            }
            $client->close();

            if ($headers instanceof \Laminas\Http\Headers){
                $headers = $headers->toArray();
            }

            $headerKey = 'content-encoding';
            $encoding = isset($headers[$headerKey]) ? (string) $headers[$headerKey] : ''; 
            if (strpos($encoding, 'gzip') === 0 || strpos($encoding, 'deflate') !== false) {
                $message = 'GZIP is enabled';
                $cssClass = 'message-success';
            } elseif ($client->getErrno()) {
                $message = 'Cannot test GZIP';
                $note = $client->getError();
                $cssClass = 'message-error';
            } else {
                $message = 'Cannot test GZIP';
                $note = 'Check your server manually';
                $cssClass = 'message-error';
            }
        }

        $apiUrl = 'https://www.giftofspeed.com/gzip-test/';
        return '<a href="' . $apiUrl . '" class="message ' . $cssClass . '">' . $message . '</a>' .
        ($note ? '<p class="message note"><span>' . $note . '</span></p>' : '');
    }
}

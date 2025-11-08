<?php
namespace Swissup\Amp\Plugin\Framework;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;

class ViewResultPage
{
    /**
     * @var \Swissup\Amp\Helper\Data
     */
    protected $helper;

    /**
     * @var \Swissup\Amp\Model\Html\Filter
     */
    protected $ampHtmlFilter;

    /**
     * @param \Swissup\Amp\Helper\Data $helper
     * @param \Swissup\Amp\Model\Html\FilterFactory $ampHtmlFilterFactory
     */
    public function __construct(
        \Swissup\Amp\Helper\Data $helper,
        \Swissup\Amp\Model\Html\FilterFactory $ampHtmlFilterFactory
    ) {
        $this->helper = $helper;
        $this->ampHtmlFilter = $ampHtmlFilterFactory->create();
    }

    /**
     * Add amphtml link to head
     *
     * @param ResultInterface $subject
     * @param ResponseInterface $httpResponse
     * @return ResultInterface
     */
    public function beforeRenderResult(
        ResultInterface $subject,
        ResponseInterface $httpResponse
    ) {
        if ($this->helper->isAmpEnabled() && $this->helper->isPageSupported()) {
            $subject->getConfig()->addRemotePageAsset(
                $this->helper->getAmpUrl(),
                'amphtml',
                ['attributes' => ['rel' => 'amphtml']]
            );
        }

        return null;
    }

    /**
     * Convert html to amphtml
     *
     * @param ResultInterface $subject
     * @param ResultInterface $result
     * @param ResponseInterface $httpResponse
     * @return ResultInterface
     */
    public function afterRenderResult(
        ResultInterface $subject,
        ResultInterface $result,
        ResponseInterface $httpResponse
    ) {
        if (!$this->helper->canUseAmp()) {
            return $result;
        }

        $html = $httpResponse->getBody();
        if (empty($html)) {
            return $result;
        }

        $html = $this->ampHtmlFilter->process($html);
        $httpResponse->setBody($html);

        return $result;
    }
}

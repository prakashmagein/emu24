<?php
namespace Swissup\Easybanner\Controller\Click;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * Get extension statistic model
     * @var \Swissup\Easybanner\Model\BannerStatistic
     */
    protected $_statistic;

    /**
     * Get extension banner model
     * @var \Swissup\Easybanner\Model\Banner
     */
    protected $_banner;

    /**
     * @var \Magento\Framework\Controller\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Swissup\Testimonials\Helper\Config $configHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory,
        \Swissup\Easybanner\Model\BannerStatistic $statistic,
        \Swissup\Easybanner\Model\Banner $banner
    )
    {
        $this->_statistic = $statistic;
        $this->_banner = $banner;
        $this->resultForwardFactory = $resultForwardFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $bannerId = (int) $this->_request->getParam('id');
        if (!$bannerId) {
            return $this->resultForwardFactory->create()->forward('noroute');
        }

        $this->_banner->load($bannerId);
        if (!$this->_banner->getId()) {
            return $this->resultForwardFactory->create()->forward('noroute');
        }

        if ($this->_banner->getIsTrackable()) {
            $this->_statistic->incrementClicksCount($this->_banner->getId());
        }

        $redirectUrl = $this->_banner->getUrl();
        if (strpos($redirectUrl, 'www.') === 0) {
            $redirectUrl = 'http://' . $this->_banner->getUrl();
        } elseif (strpos($redirectUrl, 'http://') !== 0
            && strpos($redirectUrl, 'https://') !== 0) {

            if (false === strpos($redirectUrl, '.html')) {
                $redirectUrl = $this->_url->getUrl($redirectUrl);
            } else {
                // hotfix for catalog links.
                // @todo: add ability to contol method that should be used?
                $redirectUrl = $this->_url->getDirectUrl($redirectUrl);
            }
        }

        return $this->resultRedirectFactory->create()->setUrl($redirectUrl);
    }
}

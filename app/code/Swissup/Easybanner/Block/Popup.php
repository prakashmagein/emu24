<?php

namespace Swissup\Easybanner\Block;

use Magento\Framework\View\Element\Template;

class Popup extends Template
{
    private $_bannerCollection;
    private $_jsonEncoder;
    /**
     * @var string
     */
    protected $_template = 'popup.phtml';

    /**
     * @var \Swissup\Easybanner\Helper\Data
     */
    private $helper;

    /**
     * @param Template\Context $context
     * @param \Swissup\Easybanner\Model\ResourceModel\Banner\CollectionFactory $bannerCollection
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Swissup\Easybanner\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Swissup\Easybanner\Model\ResourceModel\Banner\CollectionFactory $bannerCollection,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Swissup\Easybanner\Helper\Data $helper,
        array $data = []
    ) {
        $this->_bannerCollection = $bannerCollection;
        $this->_jsonEncoder = $jsonEncoder;
        $this->helper = $helper;

        parent::__construct($context, $data);
    }

    public function getBanners()
    {
        $_bannerCollection = $this->_bannerCollection->create();

        $_bannerCollection->getSelect()
            ->where('type in (?)', [2, 3])
            ->where('status = ?', 1);

        return $_bannerCollection->load();
    }

    public function getJsonConditions($conditions)
    {
        return $this->_jsonEncoder->encode($conditions);
    }

    public function getCookieName()
    {
        return $this->helper->getCookieName();
    }
}

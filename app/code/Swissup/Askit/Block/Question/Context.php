<?php

namespace Swissup\Askit\Block\Question;

class Context implements \Magento\Framework\ObjectManager\ContextInterface
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Swissup\Askit\Helper\Config
     */
    protected $configHelper;

    /**
     * @var \Swissup\Askit\Helper\Url
     */
    protected $urlHelper;

    /**
     * @var \Swissup\Askit\Model\VoteFactory
     */
    protected $voteFactory;

    /**
     * @var \Magento\Framework\Data\Helper\PostHelper
     */
    protected $postDataHelper;

    /**
     * @var \Magento\Framework\View\Element\Template\Context
     */
    protected $originalContext;

    /**
     * @param \Magento\Framework\Registry                      $registry
     * @param \Magento\Customer\Model\SessionFactory           $customerSessionFactory
     * @param \Swissup\Askit\Helper\Config                     $configHelper
     * @param \Swissup\Askit\Helper\Url                        $urlHelper
     * @param \Swissup\Askit\Model\VoteFactory                 $voteFactory
     * @param \Magento\Framework\Data\Helper\PostHelper        $postDataHelper
     * @param \Magento\Framework\View\Element\Template\Context $context
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        \Swissup\Askit\Helper\Config $configHelper,
        \Swissup\Askit\Helper\Url $urlHelper,
        \Swissup\Askit\Model\VoteFactory $voteFactory,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Framework\View\Element\Template\Context $context
    ) {
        $this->registry = $registry;
        $this->customerSession = $customerSessionFactory->create();
        $this->configHelper = $configHelper;
        $this->urlHelper = $urlHelper;
        $this->voteFactory = $voteFactory;
        $this->postDataHelper = $postDataHelper;
        $this->originalContext = $context;
    }

    /**
     * @return \Magento\Framework\Registry
     */
    public function getRegistry()
    {
        return $this->registry;
    }

    /**
     * @return \Magento\Customer\Model\Session
     */
    public function getCustomerSession()
    {
        return $this->customerSession;
    }

    /**
     * @return \Swissup\Askit\Helper\Config
     */
    public function getConfigHelper()
    {
        return $this->configHelper;
    }

    /**
     * @return \Swissup\Askit\Helper\Url
     */
    public function getUrlHelper()
    {
        return $this->urlHelper;
    }

    /**
     * @return \Swissup\Askit\Model\VoteFactory
     */
    public function getVoteFactory()
    {
        return $this->voteFactory;
    }

    /**
     * @return \Magento\Framework\Data\Helper\PostHelper
     */
    public function getPostDataHelper()
    {
        return $this->postDataHelper;
    }

    /**
     * @return \Magento\Framework\View\Element\Template\Context
     */
    public function getOriginalContext()
    {
        return $this->originalContext;
    }
}

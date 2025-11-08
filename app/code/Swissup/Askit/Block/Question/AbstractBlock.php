<?php

namespace Swissup\Askit\Block\Question;

use Swissup\Askit\Api\Data\MessageInterface;

class AbstractBlock extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * Customer session
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Swissup\Askit\Helper\Config
     */
    protected $_configHelper;

    /**
     * @var \Swissup\Askit\Helper\Url
     */
    protected $_urlHelper;

    /**
     * @var int
     */
    protected $_itemTypeId = 0;

    /**
     *
     * @var \Swissup\Askit\Model\VoteFactory
     */
    protected $_voteFactory;

    /**
     * @var \Magento\Framework\Data\Helper\PostHelper
     */
    private $postDataHelper;

    /**
     * @param Context $context
     * @param array   $data
     */
    public function __construct(
        Context $context,
        array $data = []
    ) {
        $this->_registry = $context->getRegistry();
        $this->_customerSession = $context->getCustomerSession();
        $this->_configHelper = $context->getConfigHelper();
        $this->_urlHelper = $context->getUrlHelper();
        $this->_voteFactory = $context->getVoteFactory();
        $this->postDataHelper = $context->getPostDataHelper();
        parent::__construct($context->getOriginalContext(), $data);
    }

    /**
     * Get review product post action
     *
     * @return string
     */
    public function getAction()
    {
        return $this->getUrl('askit/question/save');
    }

    /**
     * @return \Magento\Customer\Model\Session
     */
    protected function getCustomerSession()
    {
        return $this->_customerSession;
    }

    /**
     * @return bool
     */
    public function isCustomerLoggedIn()
    {
        return $this->getCustomerSession()->isLoggedIn();
    }

    /**
     * Get product id
     *
     * @return int
     */
    public function getProductId()
    {
        $request = $this->getRequest();
        return $request->getParam('product_id', $request->getParam('id', false));
    }

    /**
     * @return int
     */
    public function getCategoryId()
    {
        return $this->getRequest()->getParam('id', false);
    }

    /**
     * @return int
     */
    public function getPageId()
    {
        /** @var \Magento\Framework\App\Request\Http $request */
        $request = $this->getRequest();
        if ($request->getFullActionName() === 'cms_index_index') {
            return $this->getConfigHelper()->getHomePageId();
        }

        return $request->getParam('page_id', $request->getParam('id', false));
    }

    /**
     * @param int $itemTypeId
     */
    public function setItemTypeId($itemTypeId)
    {
        $this->_itemTypeId = (int) $itemTypeId;
        return $this;
    }

    /**
     * @return int
     */
    public function getItemTypeId()
    {
        // page type from request parameter
        if (0 == $this->_itemTypeId) {
            $this->_itemTypeId = $this->getRequest()->getParam('item_type_id', $this->_itemTypeId);
        }
        //page type auto detecting
        if (0 == $this->_itemTypeId) {
            /** @var \Magento\Framework\App\Request\Http $request */
            $request = $this->getRequest();
            $fullActionName = $request->getFullActionName();
            switch ($fullActionName) {
                case 'catalog_category_view':
                    $this->_itemTypeId = MessageInterface::TYPE_CATALOG_CATEGORY;
                    break;
                case 'catalog_product_view':
                case 'checkout_cart_configure':
                case 'review_product_list':
                    $this->_itemTypeId = MessageInterface::TYPE_CATALOG_PRODUCT;
                    break;
                case 'cms_page_view':
                case 'cms_index_index':
                    $this->_itemTypeId = MessageInterface::TYPE_CMS_PAGE;
                    break;
                case 'askit_customer_index':
                default:
                    $this->_itemTypeId = $this->fallbackItemTypeResolver();
                    break;
            }
        }

        return $this->_itemTypeId;
    }

    /**
     * @return int
     */
    private function fallbackItemTypeResolver()
    {
        if ($this->_registry->registry('current_product')) {
            return MessageInterface::TYPE_CATALOG_PRODUCT;
        }

        return MessageInterface::TYPE_UNKNOWN;
    }

    /**
     * @return int
     */
    public function getItemId()
    {
        $type = $this->getItemTypeId();
        switch ($type) {
            case MessageInterface::TYPE_CATALOG_CATEGORY:
                $itemId = $this->getCategoryId();
                break;
            case MessageInterface::TYPE_CATALOG_PRODUCT:
                $itemId = $this->getProductId();
                break;
            case MessageInterface::TYPE_CMS_PAGE:
                $itemId = $this->getPageId();
                break;
            default:
                $itemId = -1;
                break;
        }
        return $itemId;
    }

    /**
     * @return \Swissup\Askit\Helper\Config
     */
    public function getConfigHelper()
    {
        return $this->_configHelper;
    }

    /**
     * @return \Swissup\Askit\Helper\Url
     */
    public function getUrlHelper()
    {
        return $this->_urlHelper;
    }

    /**
     * @return \Magento\Framework\Data\Helper\PostHelper
     */
    public function getPostDataHelper()
    {
        return $this->postDataHelper;
    }

    /**
     * @param  int $id
     * @return boolean
     */
    public function canVoted($id)
    {
        if (!$this->isCustomerLoggedIn()) {
            return false;
        }
        $customerId = (int) $this->getCustomerSession()->getId();

        $model = $this->_voteFactory->create();
        if ($model->isVoted($id, $customerId)) {
            return false;
        }
        return true;
    }

    /**
     * Escape a string for the HTML attribute context
     *
     * @param string $string
     * @param boolean $escapeSingleQuote
     * @return string
     */
    public function escapeHtmlAttr($string, $escapeSingleQuote = true)
    {
        if (method_exists($this->_escaper, 'escapeHtmlAttr')) {
            return $this->_escaper->escapeHtmlAttr($string, $escapeSingleQuote);
        }

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        return htmlspecialchars($string, ENT_COMPAT, 'UTF-8', false);
    }

    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        if ($this->_configHelper->isEnabled()) {
            return parent::_toHtml();
        }

        return '';
    }
}

<?php
namespace Swissup\Askit\Helper;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Helper;
use Magento\Store\Model\ScopeInterface;

class Config extends Helper\AbstractHelper
{
    const MODULE_ENABLED             = 'askit/general/enabled';
    const DEFAULT_QUESTION_STATUS    = 'askit/general/defaultQuestionStatus';
    const DEFAULT_ANSWER_STATUS      = 'askit/general/defaultAnswerStatus';
    const ALLOWED_GUEST_QUESTION     = 'askit/general/allowedGuestQuestion';
    const ALLOWED_CUSTOMER_ANSWER    = 'askit/general/allowedCustomerAnswer';
    const ALLOWED_GUEST_ANSWER       = 'askit/general/allowedGuestAnswer';
    const ALLOWED_HINT               = 'askit/general/allowedHint';
    const ALLOWED_SHARE_CUSTOMERNAME = 'askit/general/shareCustomerName';
    const ALLOWED_SHARE_ITEM         = 'askit/general/shareItem';
    const ALLOWED_ENABLE_GRAVATAR    = 'askit/general/gravatar';
    const ALLOWED_ENABLE_NOQUESTIONS = 'askit/general/noquestions';
    const QUESTINS_PAGE_ENTITY_QS    = 'askit/questions_page/entity_questions';

    /**
     * @var \Magento\Cms\Model\Page
     */
    protected $page;

    /**
     * @param \Magento\Cms\Model\Page $page
     * @param Helper\Context          $context
     */
    public function __construct(
        \Magento\Cms\Model\Page $page,
        Helper\Context $context
    ) {
        $this->page = $page;
        parent::__construct($context);
    }

    /**
     *
     * @param  string $path
     * @param  string $scope
     * @return string
     */
    public function getConfig($path, $scope = ScopeInterface::SCOPE_STORE)
    {
        return (string) $this->scopeConfig->getValue($path, $scope);
    }

    /**
     *
     * @param  string $path
     * @param  string $scope
     * @return boolean
     */
    public function isSetFlag($path, $scope = ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->isSetFlag($path, $scope);
    }

    /**
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->isSetFlag(self::MODULE_ENABLED);
    }

    /**
     *
     * @return string
     */
    public function getDefaultQuestionStatus()
    {
        return $this->getConfig(self::DEFAULT_QUESTION_STATUS);
    }

    /**
     *
     * @return string
     */
    public function getDefaultAnswerStatus()
    {
        return $this->getConfig(self::DEFAULT_ANSWER_STATUS);
    }

    /**
     *
     *
     * @return boolean
     */
    public function isAllowedGuestQuestion()
    {
        return $this->isSetFlag(self::ALLOWED_GUEST_QUESTION);
    }

    /**
     *
     * @return boolean
     */
    public function isAllowedCustomerQuestion()
    {
        return $this->isSetFlag(self::ALLOWED_CUSTOMER_ANSWER);
    }

    /**
     *
     * @return boolean
     */
    public function isAllowedGuestAnswer()
    {
        return $this->isSetFlag(self::ALLOWED_GUEST_ANSWER);
    }

    /**
     *
     * @return boolean
     */
    public function isAllowedHint()
    {
        return $this->isSetFlag(self::ALLOWED_HINT);
    }

    /**
     *
     * @return boolean
     */
    public function isAllowedShareCustomerName()
    {
        return $this->isSetFlag(self::ALLOWED_SHARE_CUSTOMERNAME);
    }

    /**
     *
     * @return boolean
     */
    public function isAllowedShareItem()
    {
        return $this->isSetFlag(self::ALLOWED_SHARE_ITEM);
    }

    /**
     *
     * @return boolean
     */
    public function isEnabledGravatar()
    {
        return $this->isSetFlag(self::ALLOWED_ENABLE_GRAVATAR);
    }

    /**
     *
     * @return boolean
     */
    public function isEnabledNoQuestions()
    {
        return $this->isSetFlag(self::ALLOWED_ENABLE_NOQUESTIONS);
    }

    /**
     * @return int
     */
    public function getHomePageId()
    {
        return (int) $this->page->getId();
    }

    /**
     * @return boolean
     */
    public function isAllowedPageEntityQuestions()
    {
        return $this->isSetFlag(self::QUESTINS_PAGE_ENTITY_QS);
    }

    public function isPageEntityQuestionsRequiresData()
    {
        return $this->getConfig(self::QUESTINS_PAGE_ENTITY_QS) === '2';
    }
}

<?php
namespace Swissup\Askit\Controller\Vote;

use Swissup\Askit\Api\Data\MessageInterface;

class Inc extends \Magento\Framework\App\Action\Action
{
    /**
     * @var integer
     */
    protected $voteStep = 1;

    /**
     * Customer session
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $formKeyValidator;

    /**
     * @var \Swissup\Askit\Model\MessageFactory
     */
    protected $messageFactory;

    /**
     * @var \Swissup\Askit\Model\VoteFactory
     */
    protected $voteFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Swissup\Askit\Model\MessageFactory $messageFactory
     * @param \Swissup\Askit\Model\VoteFactory $voteFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Swissup\Askit\Model\MessageFactory $messageFactory,
        \Swissup\Askit\Model\VoteFactory $voteFactory
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->formKeyValidator = $formKeyValidator;
        $this->messageFactory = $messageFactory;
        $this->voteFactory = $voteFactory;
    }

    protected function _redirectReferer()
    {
        $expanded = $this->getRequest()->getParam('expanded', '');
        $this->customerSession->setAskitExpanded($expanded);
        $this->_redirect($this->_redirect->getRedirectUrl());
    }

    /**
     * Post user question
     *
     * @inherit
     */
    public function execute()
    {
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            $this->messageManager->addErrorMessage(
                __('Your session has expired')
            );

            return $this->_redirectReferer();
        }

        $id = (int) $this->getRequest()->getParam('id');
        if (!$id || !$this->customerSession->isLoggedIn()) {
            $this->messageManager->addErrorMessage(
                __('Sorry, only logged in customer can vote.')
            );
            return $this->_redirectReferer();
        }

        try {
            $customerId = $this->customerSession->getId();

            /** @var \Swissup\Askit\Model\Vote $modelVote */
            $modelVote = $this->voteFactory->create();
            if ($modelVote->isVoted($id, $customerId)) {
                $this->messageManager->addErrorMessage(
                    __('Sorry, already voted')
                );
                return $this->_redirectReferer();
            }

            /** @var \Swissup\Askit\Model\Message $modelMessage */
            $modelMessage = $this->messageFactory->create();
            $modelMessage->load($id);

            $modelMessage->setHint($modelMessage->getHint() + $this->voteStep);
            $modelMessage->save();

            $modelVote->setData([
                'message_id' => $modelMessage->getId(),
                'customer_id' => $customerId
            ])->save();

        } catch (\Exception $e) {
            // $this->inlineTranslation->resume();
            $this->messageManager->addErrorMessage(
                __('We can\'t process your request right now. Sorry, that\'s all we know.')
            );
        }

        return $this->_redirectReferer();
    }
}

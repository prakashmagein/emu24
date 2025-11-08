<?php

namespace Swissup\Navigationpro\Controller\Adminhtml\Item;

use Swissup\Navigationpro\Model\ItemFactory;
use Swissup\Navigationpro\Ui\Component\Form\Menu\TreeFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\Result\JsonFactory;

class Move extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Swissup_Navigationpro::item_save';

    /**
     * @var ItemFactory
     */
    protected $itemFactory;

    /**
     * @var TreeFactory
     */
    protected $treeFactory;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @param Context $context
     * @param ItemFactory $itemFactory
     * @param TreeFactory $treeFactory
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        ItemFactory $itemFactory,
        TreeFactory $treeFactory,
        JsonFactory $resultJsonFactory
    ) {
        $this->itemFactory = $itemFactory;
        $this->treeFactory = $treeFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $id         = (int) $this->getRequest()->getParam('item_id');
        $parentId   = (int) $this->getRequest()->getParam('target_id');
        $siblingId  = (int) $this->getRequest()->getParam('sibling_id');

        $item = $this->itemFactory->create()->load($id);
        if (!$item->getId()) {
            throw new LocalizedException(__('This item no longer exists.'));
        }

        $sibling = false;
        if ($siblingId) {
            $sibling = $this->itemFactory->create()->load($siblingId);
            if (!$sibling->getId()) {
                throw new LocalizedException(__('This item no longer exists.'));
            }
        }

        $error = true;
        try {
            $item
                ->setIsMoved(true)
                ->setParentId($parentId ? $parentId : null)
                ->setInsertBefore($sibling)
                ->setSkipContentUpdate(true)
                ->save();

            $error = false;
        } catch (LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __($e->getMessage()));
            $this->messageManager->addException($e, __('Something went wrong while saving item.'));
        }

        $messages = [];
        foreach ($this->messageManager->getMessages(true)->getItems() as $message) {
            $messages[] = $message->getText();
        }

        return $this->resultJsonFactory->create()->setData([
            'items' => $this->treeFactory->create()->toOptionArray(),
            'error' => $error,
            'messages' => $messages
        ]);
    }
}

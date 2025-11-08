<?php

namespace Swissup\Navigationpro\Controller\Adminhtml\Menu;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Swissup\Navigationpro\Ui\DataProvider\Form\MenuDataProvider;

class Settings extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Swissup_Navigationpro::menu_edit';

    /**
     * @var MenuDataProvider
     */
    protected $menuDataProvider;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @param Context $context
     * @param MenuDataProvider $menuDataProvider,
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        MenuDataProvider $menuDataProvider,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->menuDataProvider = $menuDataProvider;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = [];
        $error = true;

        try {
            $data = $this->menuDataProvider->getData();
            $error = false;
        } catch (LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __($e->getMessage()));
        }

        $messages = [];
        foreach ($this->messageManager->getMessages(true)->getItems() as $message) {
            $messages[] = $message->getText();
        }

        return $this->resultJsonFactory->create()->setData([
            'data' => current($data),
            'error' => $error,
            'messages' => $messages
        ]);
    }
}

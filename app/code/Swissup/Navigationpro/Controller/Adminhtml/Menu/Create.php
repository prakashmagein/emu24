<?php

namespace Swissup\Navigationpro\Controller\Adminhtml\Menu;

use Magento\Backend\App\Action\Context;
use Swissup\Navigationpro\Model\Menu\BuilderFactory;
use Swissup\Navigationpro\Model\Config\Source\BuilderType;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\ResultFactory;

class Create extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Swissup_Navigationpro::menu_save';

    /**
     * @var BuilderFactory
     */
    protected $builderFactory;

    /**
     * @param Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        Context $context,
        BuilderFactory $builderFactory
    ) {
        $this->builderFactory = $builderFactory;
        parent::__construct($context);
    }

    /**
     * Create action
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $type           = $this->getRequest()->getParam('type');
        $identifier     = $this->getRequest()->getParam('identifier');
        $rootCategoryId = $this->getRequest()->getParam('root_category_id');
        $configScopes   = $this->getRequest()->getParam('config_scopes', []);
        $storeIds       = $this->getRequest()->getParam('store_ids');

        try {
            $builder = $this->builderFactory->create($type)
                ->setSetting('identifier', $identifier)
                ->setSetting('config_scopes', $configScopes)
                ->setThemeId($this->getRequest()->getParam('theme_id'));

            if (is_array($storeIds)) {
                $builder->setStoreIds($storeIds);
            }

            if ($rootCategoryId) {
                $builder->setRootCategoryId($rootCategoryId);
            }

            $menu = $builder->save();

            return $resultRedirect->setPath('*/*/edit', ['menu_id' => $menu->getId()]);
        } catch (LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Something went wrong while saving menu.'));
        }

        return $resultRedirect->setPath('*/*/');
    }
}

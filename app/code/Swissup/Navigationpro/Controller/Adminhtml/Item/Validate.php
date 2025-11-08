<?php

namespace Swissup\Navigationpro\Controller\Adminhtml\Item;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Swissup\Navigationpro\Model\MenuRepository;

class Validate extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Swissup_Navigationpro::item_save';

    /**
     * @var MenuRepository
     */
    protected $menuRepository;

    /**
     * @param Context $context
     * @param MenuRepository $menuRepository
     */
    public function __construct(
        Context $context,
        MenuRepository $menuRepository
    ) {
        $this->menuRepository = $menuRepository;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $menuId = $this->getRequest()->getParam('menu_id');
        $data = $this->getRequest()->getPostValue();

        $response = new \Magento\Framework\DataObject();
        $response->setError(0);

        try {
            $this->menuRepository->getById($menuId)->validateRecursiveCalls([
                'Dropdown Settings' => $data['dropdown_settings']['layout'] ?? '',
                'Name as Html' => $data['html'] ?? '',
            ]);
        } catch (\Magento\Framework\Validation\ValidationException $e) {
            $response->setError(1);
            $response->setMessage($e->getMessage());
        }

        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($response);

        return $resultJson;
    }
}

<?php
namespace Swissup\SoldTogether\Observer;

use Magento\Framework\App\ObjectManager;
use Swissup\SoldTogether\Model\DataPacker;

class ProductSaveCreateRelationsObserver extends AbstractObserver
{
    /**
     * Create order relations
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $resource = [
            'order' => $this->orderModel->getResource(),
            'customer' => $this->customerModel->getResource()
        ];
        $controller = $observer->getEvent()->getController();
        $product = $observer->getEvent()->getProduct();
        $dataPacker = ObjectManager::getInstance()->get(DataPacker::class);
        $data = $controller->getRequest()->getParam('soldtogether', []);
        foreach ($data as $linkType => $packedData) {
            $linkData = $dataPacker->setFromPackedJson($packedData)->get();
            if (!isset($resource[$linkType])) {
                continue;
            }

            $resource[$linkType]->saveLinkedData(
                $product->getId(),
                $linkData
            );
        }

        return $this;
    }
}

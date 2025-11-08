<?php
namespace Swissup\Easybanner\Controller\Adminhtml\Banner;

use Magento\Backend\App\Action;
use Magento\Framework\App\Request\DataPersistorInterface;

class Save extends Action
{
    const ADMIN_RESOURCE = 'Swissup_Easybanner::banner_save';

    /**
     * Image uploader
     *
     * @var \Magento\Catalog\Model\ImageUploader
     */
    protected $imageUploader;

    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @param Action\Context                       $context
     * @param \Magento\Catalog\Model\ImageUploader $imageUploader
     */
    public function __construct(
        Action\Context $context,
        \Magento\Catalog\Model\ImageUploader $imageUploader,
        DataPersistorInterface $dataPersistor
    ) {
        $this->imageUploader = $imageUploader;
        $this->dataPersistor = $dataPersistor;

        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $id = $this->getRequest()->getParam('banner_id');
            /** @var \Swissup\Easybanner\Model\Banner $model */
            $model = $this->_objectManager->create('Swissup\Easybanner\Model\Banner');

            if (empty($data['banner_id'])) {
                $data['banner_id'] = null;
            }

            if ($id) {
                $model->load($id);
            }

            if (isset($data['rule'])) {
                $data['conditions'] = $data['rule']['conditions'];
                unset($data['rule']); // @phpstan-ignore-line
            }

            $data['sizes'] = $data['sizes'] ?? [];

            $model->loadPost($data);

            $imageName = '';
            if (isset($data['image']) && is_array($data['image'])) {
                $imageName = isset($data['image'][0]['name']) ? $data['image'][0]['name'] : '';
                if (isset($data['image'][0]['tmp_name'])) {
                    try {
                        $this->imageUploader->moveFileFromTmp($imageName, true);
                    } catch (\Exception $e) {
                        //
                    }
                }
            }
            $model->setImage($imageName);

            try {
                $model->save();
                $this->messageManager->addSuccess(__('Banner has been saved.'));
                $this->dataPersistor->clear('easybanner_banner');

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', [
                        'banner_id' => $model->getId(),
                        '_current' => true
                    ]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->messageManager->addException($e, __('Something went wrong while saving the banner.'));
            }

            $this->dataPersistor->set('easybanner_banner', $data);
            return $resultRedirect->setPath('*/*/edit', [
                'banner_id' => $this->getRequest()->getParam('banner_id')
            ]);
        }

        return $resultRedirect->setPath('*/*/');
    }
}

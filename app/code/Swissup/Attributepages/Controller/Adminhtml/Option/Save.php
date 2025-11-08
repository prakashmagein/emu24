<?php
namespace Swissup\Attributepages\Controller\Adminhtml\Option;

use Swissup\Attributepages\Model\ImageData;

class Save extends \Swissup\Attributepages\Controller\Adminhtml\AbstractSave
{
    const ADMIN_RESOURCE = 'Swissup_Attributepages::option_save';

    /**
     * Save action
     */
    public function execute()
    {
        if (!$data = $this->getRequest()->getPostValue()) {
            $this->_redirect('*/*/');
            return;
        }

        unset($data['stores']);
        unset($data['parent_page']);
        unset($data['parent_page_identifier']);

        $model = $this->_objectManager->create('Swissup\Attributepages\Model\Entity');
        if ($id = $this->getRequest()->getParam('entity_id')) {
            $model->load($id);
        }

        $this->dataPersistor->set('attributepages_option', $data);

        if (!$this->_validatePostData($data)) {
            $this->_redirect('*/*/edit', ['entity_id' => $model->getId(), '_current' => true]);
            return;
        }

        try {
            $mediaPath = $this->getBaseDir(ImageData::ENTITY_MEDIA_PATH);
            foreach (['image', 'thumbnail'] as $key) {
                $imageName = $data[$key][0]['name'] ?? null;

                if ($imageName && isset($data[$key][0]['tmp_name'])) {
                    try {
                        $this->imageUploader->moveFileFromTmp($imageName, true);
                    } catch (\Exception $e) {
                        $this->messageManager->addError($e->getMessage());
                    }
                }

                if (!$imageName && $model->getData($key)) {
                    $this->ioFile->rm($mediaPath . '/' . trim($model->getData($key), '/'));
                }

                $data[$key] = $imageName;
            }

            $model->addData($data);
            $model->save();

            $this->messageManager->addSuccess(__('The page has been saved.'));
            $this->dataPersistor->clear('attributepages_option');

            return $this->afterSave($model);
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        return $this->_redirect('*/*/edit', ['entity_id' => $model->getId(), '_current' => true]);
    }
}

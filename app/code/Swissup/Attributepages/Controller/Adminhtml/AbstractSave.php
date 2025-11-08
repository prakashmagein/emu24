<?php
namespace Swissup\Attributepages\Controller\Adminhtml;

use Magento\Backend\App\Action\Context;
use \Magento\Framework\App\Filesystem\DirectoryList;

abstract class AbstractSave extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Model\Layout\Update\ValidatorFactory
     */
    protected $validatorFactory;
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $fileSystem;
    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    protected $ioFile;

    protected \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor;

    protected \Magento\Catalog\Model\ImageUploader $imageUploader;

    /**
     * @var \Swissup\Attributepages\Model\Upload
     */
    protected $uploadModel;

    protected \Swissup\Attributepages\Model\Entity\Copier $entityCopier;

    public function __construct(
        Context $context,
        \Magento\Framework\View\Model\Layout\Update\ValidatorFactory $validatorFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Filesystem $fileSystem,
        \Magento\Framework\Filesystem\Io\File $ioFile,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        \Magento\Catalog\Model\ImageUploader $imageUploader,
        \Swissup\Attributepages\Model\Upload $uploadModel,
        \Swissup\Attributepages\Model\Entity\Copier $entityCopier
    ) {
        parent::__construct($context);
        $this->validatorFactory = $validatorFactory;
        $this->storeManager = $storeManager;
        $this->fileSystem = $fileSystem;
        $this->ioFile = $ioFile;
        $this->dataPersistor = $dataPersistor;
        $this->imageUploader = $imageUploader;
        $this->uploadModel = $uploadModel;
        $this->entityCopier = $entityCopier;
    }

    /**
     * Validate post data
     *
     * @param array $data
     * @return bool Return FALSE if some item is invalid
     */
    protected function _validatePostData($data)
    {
        $errorNo = true;
        if (!empty($data['layout_update_xml'])) {
            /** @var $validatorCustomLayout \Magento\Framework\View\Model\Layout\Update\Validator */
            $validatorCustomLayout = $this->validatorFactory->create();

            try {
                if (!$validatorCustomLayout->isValid($data['layout_update_xml'])) {
                    $errorNo = false;
                }
            } catch (\Exception $e) {
                $errorNo = false;
            }

            foreach ($validatorCustomLayout->getMessages() as $message) {
                $this->messageManager->addError($message);
            }
        }

        return $errorNo;
    }

    protected function afterSave($model)
    {
        $redirectBack = $this->getRequest()->getParam('back');

        if ($redirectBack) {
            if ($redirectBack === 'duplicate') {
                $model = $this->entityCopier->copy($model);
                $this->messageManager->addSuccess(__('The page has been duplicated.'));
            }

            return $this->_redirect('*/*/edit', ['_current' => true, 'back' => null, 'entity_id' => $model->getId()]);
        }

        $this->_redirect('*/*/');
    }

    protected function duplicate()
    {
        $model = $this->_objectManager->create('Swissup\Attributepages\Model\Entity');

        if ($id = $this->getRequest()->getParam('entity_id')) {
            $model->load($id);
        }

        $model = $this->entityCopier->copy($model);

        $this->messageManager->addSuccess(__('The page has been duplicated.'));

        return $this->_redirect('*/*/edit', ['_current' => true, 'back' => null, 'entity_id' => $model->getId()]);
    }

    /**
     * get base image dir
     *
     * @return string
     */
    public function getBaseDir($path, $directoryCode = DirectoryList::MEDIA)
    {
        return $this->fileSystem
            ->getDirectoryWrite($directoryCode)
            ->getAbsolutePath($path);
    }
}

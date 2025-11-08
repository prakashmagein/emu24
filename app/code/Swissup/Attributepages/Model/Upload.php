<?php
namespace Swissup\Attributepages\Model;

use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\File\Uploader;

class Upload
{
    /**
     * uploader factory
     *
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $uploaderFactory;
    /**
     * constructor
     *
     * @param UploaderFactory $uploaderFactory
     */
    public function __construct(UploaderFactory $uploaderFactory)
    {
        $this->uploaderFactory = $uploaderFactory;
    }
    /**
     * upload file
     *
     * @param $input
     * @param $destinationFolder
     * @param $data
     * @param array $allowedExtensions
     * @return string
     * @throws \Magento\Framework\Model\Exception
     */
    public function uploadFileAndGetName($input, $destinationFolder, $data, $allowedExtensions = [])
    {
        if (!empty($data[$input]['delete'])) {
            return '';
        }

        try {
            $uploader = $this->uploaderFactory->create(['fileId' => $input]);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
            $uploader->setAllowCreateFolders(true);
            $uploader->setAllowedExtensions($allowedExtensions);
            $result = $uploader->save($destinationFolder);
            return $result['file'];
        } catch (\Exception $e) {
            if ($e->getCode() != Uploader::TMP_NAME_EMPTY &&
                $e->getMessage() !== '$_FILES array is empty') {

                throw $e;
            } elseif (isset($data[$input]['value'])) {
                return $data[$input]['value'];
            }
        }

        return false;
    }
}

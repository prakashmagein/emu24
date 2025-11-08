<?php

namespace Swissup\EasySlide\Controller\Adminhtml\Slider;

use Magento\Backend\App\Action\Context;
use Swissup\EasySlide\Model\SliderFactory;
use Swissup\EasySlide\Model\SlidesFactory;
use Swissup\EasySlide\Helper\Image;

class Duplicate extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Swissup_EasySlide::save';

    /**
     * @var SliderFactory
     */
    protected $sliderFactory;

    /**
     * @var SlidesFactory
     */
    protected $slidesFactory;

    /**
     * @var Image
     */
    protected $imageHelper;

    /**
     * @param Context $context
     * @param SliderFactory $sliderFactory
     * @param SlidesFactory $slidesFactory
     * @param Image         $imageHelper
     */
    public function __construct(
        Context $context,
        SliderFactory $sliderFactory,
        SlidesFactory $slidesFactory,
        Image $imageHelper
    ) {
        $this->sliderFactory = $sliderFactory;
        $this->slidesFactory = $slidesFactory;
        $this->imageHelper = $imageHelper;
        parent::__construct($context);
    }

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $id = $this->getRequest()->getParam('slider_id');
        if ($id) {
            try {
                $slider = $this->sliderFactory->create();
                $slider->load($id);
                $data = $slider->getData();
                unset($data['slider_id']);
                $data['identifier'] .= '_' . uniqid();
                $duplicateSlider = $this->sliderFactory->create();
                $duplicateSlider->setData($data);
                $duplicateSlider->save();
                foreach ($slider->getSlidesCollection() as $slide) {
                    $data = $slide->getData();
                    unset($data['slide_id']);
                    $data['slider_id'] = $duplicateSlider->getId();
                    $data['image'] = $this->imageHelper->duplicateImage($data['image']);
                    $duplicatedSlide = $this->slidesFactory->create();
                    $duplicatedSlide->setData($data);
                    $duplicatedSlide->save();
                }

                $this->messageManager->addSuccess(__(
                    'You duplicated the slider. New slider identifier - %1',
                    $duplicateSlider->getIdentifier()
                ));

                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());

                return $resultRedirect->setPath('*/*/', ['slider_id' => $id]);
            }
        }

        $this->messageManager->addError(__('We can\'t find an item to delete.'));

        return $resultRedirect->setPath('*/*/');
    }
}

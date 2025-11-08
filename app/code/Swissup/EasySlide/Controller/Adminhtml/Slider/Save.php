<?php

namespace Swissup\EasySlide\Controller\Adminhtml\Slider;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\ResultFactory;
use Swissup\EasySlide\Model\SliderFactory;
use Swissup\EasySlide\Model\SlidesFactory;

class Save extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Swissup_EasySlide::save';

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var SliderFactory
     */
    protected $sliderFactory;

    /**
     * @var SlidesFactory
     */
    protected $slidesFactory;

    /**
     * @param Context $context
     * @param SliderFactory $sliderFactory
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        Context $context,
        SliderFactory $sliderFactory,
        SlidesFactory $slidesFactory,
        DataPersistorInterface $dataPersistor
    ) {
        $this->sliderFactory = $sliderFactory;
        $this->slidesFactory = $slidesFactory;
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
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue('slider_config');
        $data = $data + $this->getRequest()->getPostValue();
        unset($data['slider_config']);
        if ($data) {
            if (isset($data['is_active']) && $data['is_active'] === 'true') {
                $data['is_active'] = 1;
            }
            if (empty($data['slider_id'])) {
                $data['slider_id'] = null;
            }

            $id = $this->getRequest()->getParam('slider_id');
            /** @var \Swissup\EasySlide\Model\Slider $model */
            $slider = $this->sliderFactory->create()->load($id);
            if (!$slider->getId() && $id) {
                $this->messageManager->addError(__('This slider no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }

            if (!$data['is_responsive']) {
                $data['responsive_sizes'] = '';
                $data['responsive_widths'] = '';
            }

            unset($data['is_responsive']);
            $slider->addData($data);

            try {
                $slider->save();

                if (array_key_exists('product', $data)) {
                    foreach ($data['product']['media_gallery']['images'] as $id => $item) {
                        $slideModel = $this->slidesFactory->create();
                        $slideId = $item['slide_id'];
                        if ($slideId) {
                            $slideModel->load($item['slide_id']);
                        }

                        if (array_key_exists('removed', $item)) {
                            if (1 == (int)$item['removed']) {
                                if ($slideModel->getId()) {
                                    $slideModel->delete();
                                }

                                continue;
                            }
                        }

                        $slideData = [
                            'slider_id' => $slider->getId(),
                            'title' => $item['title'],
                            'image' => $item['file'] ?? null,
                            'description' => $item['description'],
                            'desc_position' => $item['desc_position'] ?? null,
                            'desc_background' => $item['desc_background'] ?? null,
                            'url' => $item['link'] ?? null,
                            'target' => $item['target'] ?? null,
                            'sort_order' => $item['position'],
                            'is_active' => $item['is_active'],
                            'active_from_timestamp' => empty($item['active_from']) ? null : $item['active_from'],
                            'active_to_timestamp' => empty($item['active_to']) ? null : $item['active_to']
                        ];
                        $slideModel->addData($slideData);
                        $slideModel->save();
                    }
                }

                $this->messageManager->addSuccess(__('You saved slider.'));
                $this->dataPersistor->clear('easy_slide_form');
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['slider_id' => $slider->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the slider.'));
            }

            $this->dataPersistor->set('easy_slide_form', $data);
            return $resultRedirect->setPath('*/*/edit', ['slider_id' => $id]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}

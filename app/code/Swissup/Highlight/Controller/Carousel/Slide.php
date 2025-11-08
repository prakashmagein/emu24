<?php

namespace Swissup\Highlight\Controller\Carousel;

use Magento\Framework\Controller\ResultFactory;

class Slide extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Swissup\Highlight\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    private $urlHelper;

    /**
     * @var \Magento\Framework\View\Layout\Generator\Block
     */
    private $blockGenerator;

    /**
     * @param \Swissup\Highlight\Helper\Data $helper
     * @param \Magento\Framework\Url\Helper\Data $urlHelper
     * @param \Magento\Framework\View\Layout\Generator\Block $blockGenerator
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        \Swissup\Highlight\Helper\Data $helper,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Magento\Framework\View\Layout\Generator\Block $blockGenerator,
        \Magento\Framework\App\Action\Context $context
    ) {
        $this->helper = $helper;
        $this->urlHelper = $urlHelper;
        $this->blockGenerator = $blockGenerator;
        parent::__construct($context);
    }

    /**
     * @return void
     */
    public function execute()
    {
        if (!$this->_request->isAjax()) {
            return $this->_redirect('/');
        }

        $data = $this->_request->getParam('block_data', []);
        $data = $this->helper->filterBlockData($data);
        $data['disable_wrapper'] = true;

        if (!isset($data['type'])) {
            return $this->resultFactory
                ->create(ResultFactory::TYPE_JSON)
                ->setData([
                    'html' => '',
                    'isLastPage' => true,
                ]);
        }

        $type = $data['type'];
        unset($data['type']);

        if (empty($data['page_count']) || $data['page_count'] <= 1) {
            $data['page_count'] = 9999;
        }

        $this->_view->loadLayout();

        $block = $this->blockGenerator->createBlock($type, '', ['data' => $data]);
        $block->setIsShowPager(false);
        $this->getLayout()->addBlock($block, '', 'highlight.carousel.slide');

        $html = $this->getLayout()->renderElement('highlight.carousel.slide');
        $currentUrl = $this->urlHelper->getCurrentBase64Url();
        $refererUrl = $this->urlHelper->getEncodedUrl($this->_request->getParam('referer'));
        $html = str_replace($currentUrl, $refererUrl, $html);

        return $this->resultFactory
            ->create(ResultFactory::TYPE_JSON)
            ->setData([
                'html' => '<div class="swiper-slide slide">' . $html . '</div>',
                'isLastPage' => !$block->hasMorePages()
            ]);
    }

    /**
     * @return \Magento\Framework\View\LayoutInterface
     */
    private function getLayout()
    {
        return $this->_view->getLayout();
    }
}

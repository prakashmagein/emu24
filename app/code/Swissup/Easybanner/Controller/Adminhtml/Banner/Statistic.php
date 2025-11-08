<?php
namespace Swissup\Easybanner\Controller\Adminhtml\Banner;

use Magento\Backend\App\Action\Context;

class Statistic extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Swissup_Easybanner::easybanner_banner';

    /**
     * Json encoder
     *
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * statistics model
     *
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $statistic;

    /**
     * @param Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\App\ResourceConnection $resource
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Swissup\Easybanner\Model\BannerStatistic $statistic
    ) {
        $this->jsonEncoder = $jsonEncoder;
        $this->statistic = $statistic;
        parent::__construct($context);
    }
    /**
     * Get Statistics data action
     *
     */
    public function execute()
    {
        $bannerId = $this->getRequest()->getParam('banner_id');
        $type = $this->getRequest()->getParam('type');

        return $this->getResponse()->setBody(
            $this->jsonEncoder->encode([
                'finished'  => true,
                'statistic' => $this->statistic->getChartStatisticData($bannerId, $type)
            ])
        );
    }
}

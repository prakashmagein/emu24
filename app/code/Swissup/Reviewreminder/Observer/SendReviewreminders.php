<?php
namespace Swissup\Reviewreminder\Observer;

class SendReviewreminders
{
    /**
     * @var \Swissup\Reviewreminder\Helper\Helper
     */
    protected $helper;
    /**
     * @param \Swissup\Reviewreminder\Helper\Helper $helper
     */
    public function __construct(
        \Swissup\Reviewreminder\Helper\Helper $helper
    ) {
        $this->helper = $helper;
    }
    /**
     * Check and send reminder emails
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute()
    {
        $this->helper->sendReminders();
    }
}

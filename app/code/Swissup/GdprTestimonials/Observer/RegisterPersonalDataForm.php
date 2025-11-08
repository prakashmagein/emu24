<?php

namespace Swissup\GdprTestimonials\Observer;

class RegisterPersonalDataForm implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $observer->getCollection()->addItem(
            new \Swissup\Gdpr\Model\PersonalDataForm([
                'id'     => 'swissup:testimonials',
                'name'   => 'Swissup: Testimonials',
                'action' => 'testimonials_index_save',
            ])
        );
    }
}

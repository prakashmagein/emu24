<?php

namespace Swissup\GdprReviewreminder\Plugin;

use Swissup\Reviewreminder\Model\Entity;

class ReviewreminderEntity
{
    /**
     * @var \Swissup\Gdpr\Helper\Data
     */
    private $helper;

    /**
     * @param \Swissup\Gdpr\Helper\Data $helper
     */
    public function __construct(\Swissup\Gdpr\Helper\Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param Entity $subject
     * @param callable $proceed
     */
    public function beforeSave(Entity $subject)
    {
        if ($this->helper->isEmailAnonymized($subject->getCustomerEmail())) {
            $subject->setStatus(Entity::STATUS_CANCELLED);
        }
    }
}

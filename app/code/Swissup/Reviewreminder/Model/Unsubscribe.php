<?php
namespace Swissup\Reviewreminder\Model;

class Unsubscribe extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Swissup\Reviewreminder\Model\ResourceModel\Unsubscribe::class);
    }

    /**
     * Get order_date
     *
     * return string
     */
    public function getEmail()
    {
        return $this->getData('email');
    }

    /**
     * Set email
     *
     * @param string $email
     * return $this
     */
    public function setEmail($email)
    {
        return $this->setData('email', $email);
    }
}

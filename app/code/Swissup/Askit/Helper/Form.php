<?php
namespace Swissup\Askit\Helper;

use Magento\Contact\Helper\Data as ContactHelper;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ObjectManager;

class Form extends ContactHelper
{
    protected $formId;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var array
     */
    protected $postData = null;

    public function setFormId($formId)
    {
        $this->formId = $formId;
        return $this;
    }

    /**
     * Get user name
     *
     * @return string
     */
    public function getUserName()
    {
        $userName = $this->getPostValue('customer_name');
        if (!empty($userName)) {
            return $userName;
        }
        return parent::getUserName();
    }

    /**
     * Get user email
     *
     * @return string
     */
    public function getUserEmail()
    {
        $userName = $this->getPostValue('email');
        if (!empty($userName)) {
            return $userName;
        }
        return parent::getUserEmail();
    }

    /**
     *
     * @param  string $key
     * @return string
     */
    public function getPostValue($key)
    {
        $formId = $this->formId;//'swissup_askit_new_question_form';
        if (!isset($this->postData[$formId])) {
            $dataPersistor = $this->getDataPersistor();
            $postData = [];
            if ($dataPersistor) {
                $postData = (array) $this->getDataPersistor()->get($formId);
                // $this->getDataPersistor()->clear($formId);
            }
            $this->postData[$formId] = $postData;
        }

        if (isset($this->postData[$formId][$key])) {
            return (string) $this->postData[$formId][$key];
        }

        return '';
    }

    /**
     * Get Data Persistor
     *
     * @return DataPersistorInterface|boolean
     */
    protected function getDataPersistor()
    {
        $class = \Magento\Framework\App\Request\DataPersistor::class;
        if (!class_exists($class, false)) {
            return false;
        }

        if ($this->dataPersistor === null) {
            $this->dataPersistor = ObjectManager::getInstance()
                ->get(DataPersistorInterface::class);
        }

        return $this->dataPersistor;
    }
}

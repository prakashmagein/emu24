<?php

namespace Swissup\Gdpr\Block;

use Magento\Framework\View\Element\Template;

class Consents extends Template
{
    protected $_template = 'Swissup_Gdpr::consents.phtml';

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var \Swissup\Gdpr\Model\ResourceModel\PersonalDataForm\Collection
     */
    private $forms;

    /**
     * @var \Swissup\Gdpr\Helper\Data
     */
    private $helper;

    /**
     * @param Template\Context                         $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Swissup\Gdpr\Model\ResourceModel\PersonalDataForm\Collection $forms
     * @param \Swissup\Gdpr\Helper\Data                $helper
     * @param array                                    $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Swissup\Gdpr\Model\ResourceModel\PersonalDataForm\Collection $forms,
        \Swissup\Gdpr\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->jsonEncoder = $jsonEncoder;
        $this->forms = $forms;
        $this->helper = $helper;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        if (!$this->helper->isGdprEnabled()) {
            return '';
        }
        return $this->_template;
    }

    /**
     * @return string
     */
    public function getJsonConfig()
    {
        $result = [];
        foreach ($this->forms->getItems() as $form) {
            $consents = $form->getConsents();
            if (!$consents) {
                continue;
            }

            $result[] = $form->getJsConfig();
        }

        return $this->jsonEncoder->encode($result);
    }
}

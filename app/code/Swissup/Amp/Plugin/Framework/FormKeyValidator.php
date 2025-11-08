<?php
namespace Swissup\Amp\Plugin\Framework;

use Magento\Framework\Data\Form\FormKey\Validator;

class FormKeyValidator
{
    /**
     * @var \Swissup\Amp\Helper\Data
     */
    protected $helper;

    /**
     * @param \Swissup\Amp\Helper\Data $helper
     */
    public function __construct(
        \Swissup\Amp\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Disable form-key validation
     * @param  Validator $subject
     * @param  bool      $result
     * @return bool
     */
    public function afterValidate(
        Validator $subject,
        $result
    ) {
        if ($this->helper->canSkipFormKeyValidation()) {
            return true;
        }

        return $result;
    }
}

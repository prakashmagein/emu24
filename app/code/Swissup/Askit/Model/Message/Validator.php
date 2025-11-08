<?php
namespace Swissup\Askit\Model\Message;

use Magento\Framework\Validator\EmailAddress as EmailAddressValidator;
use Magento\Framework\Validator\NotEmpty as NotEmptyValidator;
use Magento\Framework\Validator\NotEmptyFactory as NotEmptyValidatorFactory;

class Validator
{
    /**
     * @var NotEmptyValidator
     */
    private $notEmptyValidator;

    /**
     * @var EmailAddressValidator
     */
    private $emailAddressValidator;

    /**
     * @param NotEmptyFactory $notEmptyValidatorFactory
     * @param EmailAddressValidator $emailAddressValidator
     */
    public function __construct(
        NotEmptyValidatorFactory $notEmptyValidatorFactory,
        EmailAddressValidator $emailAddressValidator
    ) {
        $this->notEmptyValidator = $notEmptyValidatorFactory->create(['options' => NotEmptyValidator::ALL]);
        $this->emailAddressValidator = $emailAddressValidator;
    }

    /**
     * @param array $data
     * @return bool
     * @throws \Magento\Framework\Validator\Exception
     */
    public function validate($data)
    {
        $error = false;
        $errorMessage = '';
        if (!isset($data['customer_name'])
            || !$this->notEmptyValidator->isValid(trim($data['customer_name']))
        ) {
            $error = true;
            $errorMessage = 'Name can\'t be empty.';
        }
        if (!isset($data['text']) || !$this->notEmptyValidator->isValid(trim($data['text']))) {
            $error = true;
            $errorMessage = 'Question can\'t be empty.';
        }
        if (!isset($data['email'])
            || empty(trim($data['email']))
            || !$this->emailAddressValidator->isValid($data['email'])
        ) {
            $error = true;
            $errorMessage = 'Email is not valid.';
        }

        if ($error) {
            throw new \Magento\Framework\Validator\Exception(__($errorMessage));
        }

        return true;
    }

    /**
     * Validates the given email address.
     *
     * @param string $email The email address to validate.
     * @return bool Returns true if the email address is valid, false otherwise.
     */
    public function validateEmail($email)
    {
        return !empty(trim($email)) && $this->emailAddressValidator->isValid($email);
    }
}

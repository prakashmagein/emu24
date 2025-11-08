<?php

namespace Swissup\Gdpr\Model;

use Swissup\Gdpr\Model\ClientRequest;

class Faker
{
    private $helper;
    /**
     * @param \Swissup\Gdpr\Helper\Data $helper
     */
    public function __construct(
        \Swissup\Gdpr\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @return array
     */
    public function getCustomerData(ClientRequest $request)
    {
        $data = $this->getAddressData($request);
        $data['dob'] = '1900-01-01 00:00:00';
        return $data;
    }

    /**
     * @return array
     */
    public function getAddressData(ClientRequest $request)
    {
        $data = array_fill_keys([
            'firstname',
            'lastname',
            'middlename',
            'company',
            'postcode',
            'street',
            'region',
            'city',
            'telephone',
            'fax',
        ], $this->getStaticPlaceholder());

        $data['email'] = $this->getEmail($request);

        return $data;
    }

    /**
     * @return array
     */
    public function getOrderData(ClientRequest $request)
    {
        $data = $this->getQuoteData($request);
        return $data;
    }

    /**
     * @return array
     */
    public function getQuoteData(ClientRequest $request)
    {
        $data = array_fill_keys([
            'customer_firstname',
            'customer_lastname',
            'customer_middlename',
        ], $this->getStaticPlaceholder());

        $data['customer_email'] = $this->getEmail($request);
        $data['customer_dob'] = '1900-01-01 00:00:00';
        $data['remote_ip'] = '127.0.0.1';

        return $data;
    }

    /**
     * @return array
     */
    public function getPaymentData(ClientRequest $request)
    {
        $data = array_fill_keys([
            'echeck_bank_name',
            'echeck_routing_number',
            'echeck_account_name',
            'cc_debug_request_body',
            'cc_debug_response_body',
            'cc_number_enc',
            'cc_last_4',
            'cc_owner',
            'po_number',
            'additional_data',
        ], $this->getStaticPlaceholder());

        $data['cc_exp_month'] = '01';
        $data['cc_exp_year'] = '1900';
        $data['cc_ss_start_month'] = '01';
        $data['cc_ss_start_year'] = '1900';
        $data['additional_information'] = '';

        return $data;
    }

    /**
     * @param  string $email
     * @return string
     */
    public function getEmail(ClientRequest $request)
    {
        return ClientRequest::ANONYMIZED_IDENTITY_PREFIX
            . base_convert($request->getId(), 10, 36)
            . ClientRequest::ANONYMIZED_IDENTITY_SUFFIX;
    }

    /**
     * @return string
     */
    public function getStaticPlaceholder()
    {
        return $this->helper->getAnonymizationPlaceholder();
    }
}

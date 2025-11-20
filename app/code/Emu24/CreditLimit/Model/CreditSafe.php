<?php
namespace Emu24\CreditLimit\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Store\Model\ScopeInterface;

class CreditSafe
{
    const XML_PATH_ENABLED = 'creditlimit/general/enable';
    const XML_PATH_MODE    = 'creditlimit/general/mode';
    const XML_PATH_USER    = 'creditlimit/general/username';
    const XML_PATH_PASS    = 'creditlimit/general/password';

    const SANDBOX_URL = 'https://connect.sandbox.creditsafe.com/v1';
    const PROD_URL    = 'https://connect.creditsafe.com/v1';

    private $curl;
    private $scopeConfig;

    public function __construct(Curl $curl, ScopeConfigInterface $scopeConfig)
    {
        $this->curl = $curl;
        $this->scopeConfig = $scopeConfig;
    }

    public function fetchCreditLimit(string $regNo): ?string
    {
        if (!$this->scopeConfig->isSetFlag(self::XML_PATH_ENABLED, ScopeInterface::SCOPE_STORE)) {
            throw new LocalizedException(__('Module disabled'));
        }

        $username = (string)$this->scopeConfig->getValue(self::XML_PATH_USER, ScopeInterface::SCOPE_STORE);
        $password = (string)$this->scopeConfig->getValue(self::XML_PATH_PASS, ScopeInterface::SCOPE_STORE);
        $mode     = (string)$this->scopeConfig->getValue(self::XML_PATH_MODE, ScopeInterface::SCOPE_STORE);

        $baseUrl = ($mode === 'production') ? self::PROD_URL : self::SANDBOX_URL;

        $authUrl = $baseUrl . '/authenticate';
        $payload = json_encode(['username' => $username, 'password' => $password]);
        $this->curl->addHeader('Content-Type', 'application/json');
        $this->curl->post($authUrl, $payload);
        $authData = $this->decodeResponse($this->curl->getBody(), $this->curl->getStatus(), __('Authentication failed'));
        $token = is_array($authData) ? ($authData['token'] ?? null) : null;
        if (!$token || !is_string($token)) {
            throw new LocalizedException(__('Authentication failed'));
        }

        $searchUrl = $baseUrl . '/companies?countries=GB&regNo=' . urlencode($regNo);
        $this->curl->reset();
        $this->curl->addHeader('Authorization', 'Bearer ' . $token);
        $this->curl->addHeader('Accept', 'application/json');
        $this->curl->get($searchUrl);
        $searchData = $this->decodeResponse($this->curl->getBody(), $this->curl->getStatus(), __('Company search failed'));

        $companyId = $this->getCompanyId($searchData);
        if (!$companyId) {
            return null;
        }

        $reportUrl = $baseUrl . '/companies/' . $companyId;
        $this->curl->reset();
        $this->curl->addHeader('Authorization', 'Bearer ' . $token);
        $this->curl->addHeader('Accept', 'application/json');
        $this->curl->get($reportUrl);
        $reportData = $this->decodeResponse($this->curl->getBody(), $this->curl->getStatus(), __('Company report failed'));

        return $this->extractCreditLimit($reportData);
    }

    private function extractCreditLimit(array $data): ?string
    {
        $value = $data['creditScore']['currentCreditRating']['creditLimit']['value'] ?? null;
        if ($value !== null) {
            return (string)$value;
        }
        return null;
    }

    private function decodeResponse(string $body, int $status, $defaultError)
    {
        if ($status >= 400) {
            throw new LocalizedException($defaultError);
        }

        $data = json_decode($body, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new LocalizedException($defaultError);
        }

        return $data;
    }

    private function getCompanyId($searchData): ?string
    {
        if (!is_array($searchData)) {
            return null;
        }

        if (isset($searchData['companies']) && is_array($searchData['companies']) && isset($searchData['companies'][0]['id'])) {
            return (string)$searchData['companies'][0]['id'];
        }

        if (isset($searchData['results']) && is_array($searchData['results']) && isset($searchData['results'][0]['id'])) {
            return (string)$searchData['results'][0]['id'];
        }

        return null;
    }
}

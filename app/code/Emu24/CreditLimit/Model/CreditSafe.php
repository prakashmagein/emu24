<?php
namespace Emu24\CreditLimit\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Store\Model\ScopeInterface;

class CreditSafe
{
    const XML_PATH_ENABLED   = 'creditlimit/general/enable';
    const XML_PATH_MODE      = 'creditlimit/general/mode';
    const XML_PATH_USER      = 'creditlimit/general/username';
    const XML_PATH_PASS      = 'creditlimit/general/password';
    const XML_PATH_COUNTRIES = 'creditlimit/general/countries';

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
        $report = $this->fetchReport($regNo);
        return $report['credit']['creditLimit']['amount'] ?? null;
    }

    public function fetchReport(string $regNo): array
    {
        if (!$this->scopeConfig->isSetFlag(self::XML_PATH_ENABLED, ScopeInterface::SCOPE_STORE)) {
            throw new LocalizedException(__('Module disabled'));
        }

        $username  = (string)$this->scopeConfig->getValue(self::XML_PATH_USER, ScopeInterface::SCOPE_STORE);
        $password  = (string)$this->scopeConfig->getValue(self::XML_PATH_PASS, ScopeInterface::SCOPE_STORE);
        $mode      = (string)$this->scopeConfig->getValue(self::XML_PATH_MODE, ScopeInterface::SCOPE_STORE);
        $countries = trim((string)$this->scopeConfig->getValue(self::XML_PATH_COUNTRIES, ScopeInterface::SCOPE_STORE));

        if ($countries === '') {
            $countries = 'GB';
        }

        $baseUrl = ($mode === 'production') ? self::PROD_URL : self::SANDBOX_URL;

        $token = $this->authenticate($baseUrl, $username, $password);

        $company = $this->searchCompany($baseUrl, $token, $regNo, $countries);
        if (!$company || !isset($company['id'])) {
            throw new LocalizedException(__('Company not found for registration number %1', $regNo));
        }

        $report = $this->fetchCompanyReport($baseUrl, $token, (string)$company['id']);

        return $this->buildReport($regNo, $countries, $company, $report);
    }

    private function authenticate(string $baseUrl, string $username, string $password): string
    {
        $this->curl->reset();
        $payload = json_encode(['username' => $username, 'password' => $password]);
        $this->curl->addHeader('Content-Type', 'application/json');
        $this->curl->addHeader('Accept', 'application/json');
        $this->curl->post($baseUrl . '/authenticate', $payload);

        $authData = $this->decodeResponse($this->curl->getBody(), $this->curl->getStatus(), __('Authentication failed'));
        $token = is_array($authData) ? ($authData['token'] ?? null) : null;
        if (!$token || !is_string($token)) {
            throw new LocalizedException(__('Authentication failed'));
        }

        return $token;
    }

    private function searchCompany(string $baseUrl, string $token, string $regNo, string $countries): array
    {
        $query = http_build_query([
            'page'      => 1,
            'pageSize'  => 10,
            'countries' => $countries,
            'regNo'     => $regNo,
        ]);

        $this->curl->reset();
        $this->curl->addHeader('Authorization', 'Bearer ' . $token);
        $this->curl->addHeader('Accept', 'application/json');
        $this->curl->get($baseUrl . '/companies?' . $query);

        $searchData = $this->decodeResponse($this->curl->getBody(), $this->curl->getStatus(), __('Company search failed'));
        $company = $this->getCompany($searchData);
        if (!$company) {
            throw new LocalizedException(__('No companies found for registration number %1', $regNo));
        }

        return $company;
    }

    private function fetchCompanyReport(string $baseUrl, string $token, string $companyId): array
    {
        $this->curl->reset();
        $this->curl->addHeader('Authorization', 'Bearer ' . $token);
        $this->curl->addHeader('Accept', 'application/json');
        $this->curl->get($baseUrl . '/companies/' . urlencode($companyId));

        return $this->decodeResponse($this->curl->getBody(), $this->curl->getStatus(), __('Company report failed'));
    }

    private function buildReport(string $regNo, string $countries, array $companyBasic, array $detailsData): array
    {
        $report = $detailsData['report'] ?? [];

        $summary      = $report['companySummary'] ?? [];
        $identBasic   = $report['companyIdentification']['basicInformation'] ?? [];
        $mainAddress  = $report['contactInformation']['mainAddress'] ?? [];
        $websites     = $report['contactInformation']['websites'] ?? [];
        $activityMain = $summary['mainActivity'] ?? $identBasic['principalActivity'] ?? [];

        $turnover     = $summary['latestTurnoverFigure'] ?? [];
        $equity       = $summary['latestShareholdersEquityFigure'] ?? [];

        $creditScoreNode     = $report['creditScore']['currentCreditRating'] ?? [];
        $creditScoreValue    = $creditScoreNode['commonValue'] ?? null;
        $creditScoreDesc     = $creditScoreNode['commonDescription'] ?? null;
        $creditLimitAmount   = $creditScoreNode['creditLimit']['value'] ?? null;
        $creditLimitCurrency = $creditScoreNode['creditLimit']['currency'] ?? null;

        $shareholdersRaw = $report['shareCapitalStructure']['shareHolders'] ?? [];
        $shareholders = [];
        foreach ($shareholdersRaw as $sh) {
            $shareholders[] = [
                'name'                  => $sh['name'] ?? null,
                'type'                  => $sh['shareholderType'] ?? null,
                'percentSharesHeld'     => $sh['percentSharesHeld'] ?? null,
                'totalNumberOfShares'   => $sh['totalNumberOfSharesOwned'] ?? null,
                'totalValueOfShares'    => $sh['totalValueOfSharesOwned'] ?? null,
                'currency'              => $sh['currency'] ?? null,
            ];
        }

        $directorsRaw = $report['directors']['currentDirectors'] ?? [];
        $directors = [];
        foreach (array_slice($directorsRaw, 0, 5) as $dir) {
            $directors[] = [
                'name'               => $dir['name'] ?? null,
                'title'              => $dir['title'] ?? null,
                'firstName'          => $dir['firstName'] ?? null,
                'surname'            => $dir['surname'] ?? null,
                'dateOfBirth'        => $dir['dateOfBirth'] ?? null,
                'nationality'        => $dir['nationality'] ?? null,
                'gender'             => $dir['gender'] ?? null,
                'occupation'         => $dir['additionalData']['occupation'] ?? null,
                'presentAppointments'=> $dir['additionalData']['presentAppointments'] ?? null,
                'address'            => $dir['address']['simpleValue'] ?? null,
                'position'           => isset($dir['positions'][0]['positionName']) ? $dir['positions'][0]['positionName'] : null,
                'dateAppointed'      => isset($dir['positions'][0]['dateAppointed']) ? $dir['positions'][0]['dateAppointed'] : null,
            ];
        }

        $group           = $report['groupStructure'] ?? [];
        $ultimateParent  = $group['ultimateParent'] ?? null;
        $immediateParent = $group['immediateParent'] ?? null;

        $employeesInfo    = $report['otherInformation']['employeesInformation'] ?? [];
        $latestEmployees  = !empty($employeesInfo) ? $employeesInfo[0] : null;

        return [
            'input' => [
                'regNoInput' => $regNo,
                'countries'  => $countries,
            ],
            'company' => [
                'companyId'                 => $summary['companyId'] ?? $companyBasic['id'] ?? null,
                'businessName'              => $summary['businessName'] ?? $identBasic['businessName'] ?? ($companyBasic['name'] ?? null),
                'registeredCompanyName'     => $identBasic['registeredCompanyName'] ?? null,
                'country'                   => $summary['country'] ?? $identBasic['country'] ?? null,
                'companyNumber'             => $summary['companyNumber'] ?? null,
                'companyRegistrationNumber' => $summary['companyRegistrationNumber'] ?? $identBasic['companyRegistrationNumber'] ?? ($companyBasic['regNo'] ?? null),
                'vatRegistrationNumber'     => $identBasic['vatRegistrationNumber'] ?? null,
                'companyRegistrationDate'   => $identBasic['companyRegistrationDate'] ?? null,
                'legalForm'                 => $identBasic['legalForm']['description'] ?? null,
                'ownershipType'             => $identBasic['ownershipType'] ?? null,
                'status'                    => $summary['companyStatus']['description']
                    ?? $identBasic['companyStatus']['description']
                    ?? null,
                'principalActivity'       => $activityMain['description'] ?? null,
                'activityCode'            => $activityMain['code'] ?? null,
                'activityClassification'  => $activityMain['classification'] ?? null,
            ],
            'contact' => [
                'registeredAddress' => [
                    'full'       => $mainAddress['simpleValue'] ?? $identBasic['contactAddress']['simpleValue'] ?? null,
                    'street'     => $mainAddress['street'] ?? $identBasic['contactAddress']['street'] ?? null,
                    'city'       => $mainAddress['city'] ?? $identBasic['contactAddress']['city'] ?? null,
                    'postalCode' => $mainAddress['postalCode'] ?? $identBasic['contactAddress']['postalCode'] ?? null,
                    'country'    => $mainAddress['country'] ?? $identBasic['country'] ?? null,
                ],
                'telephone' => $mainAddress['telephone'] ?? $identBasic['contactAddress']['telephone'] ?? null,
                'websites'  => $websites,
            ],
            'financials' => [
                'latestTurnover' => [
                    'currency' => $turnover['currency'] ?? null,
                    'value'    => $turnover['value'] ?? null,
                ],
                'latestShareholdersEquity' => [
                    'currency' => $equity['currency'] ?? null,
                    'value'    => $equity['value'] ?? null,
                ],
                'employeesLatest' => $latestEmployees,
            ],
            'credit' => [
                'creditScore' => [
                    'value'       => $creditScoreValue,
                    'description' => $creditScoreDesc,
                ],
                'creditLimit' => [
                    'amount'   => $creditLimitAmount,
                    'currency' => $creditLimitCurrency,
                ],
            ],
            'shareholders' => $shareholders,
            'directors'    => $directors,
            'group' => [
                'ultimateParent'  => $ultimateParent,
                'immediateParent' => $immediateParent,
            ],
        ];
    }

    private function decodeResponse(string $body, int $status, $defaultError)
    {
        $data = json_decode($body, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $data = null;
        }

        if ($status >= 400) {
            if (is_array($data)) {
                if (isset($data['error'])) {
                    throw new LocalizedException(__('%1', $data['error']));
                }
                if (isset($data['message'])) {
                    throw new LocalizedException(__('%1', $data['message']));
                }
            }
            throw new LocalizedException($defaultError);
        }

        if ($data === null) {
            throw new LocalizedException($defaultError);
        }

        return $data;
    }

    private function getCompany(array $searchData): ?array
    {
        if (isset($searchData['companies']) && is_array($searchData['companies']) && isset($searchData['companies'][0])) {
            return (array)$searchData['companies'][0];
        }

        if (isset($searchData['results']) && is_array($searchData['results']) && isset($searchData['results'][0])) {
            return (array)$searchData['results'][0];
        }

        return null;
    }
}

<?php
/**
 * Creditsafe API flow
 * 1. Authenticate and get Bearer token
 * 2. Search company by regNo
 * 3. Get company details by ID
 * 4. Extract credit score, credit limit & key company details
 */

// ============ CONFIG / INPUT ============
$username  = "info@pracoda.com";
$password  = "1UxT4XMT1L|5!DVIH!0P1M";

// Make regNo dynamic if you want (GET/POST/CLI etc.)
$regNo     = isset($_GET['regNo']) ? $_GET['regNo'] : '03836192';
$countries = "GB"; // or "GB,FR"

// ============ 1. AUTHENTICATION ============
$authUrl = "https://connect.sandbox.creditsafe.com/v1/authenticate";

$credentials = [
    "username" => $username,
    "password" => $password
];

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL            => $authUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST  => "POST",
    CURLOPT_HTTPHEADER     => ["Content-Type: application/json"],
    CURLOPT_POSTFIELDS     => json_encode($credentials),
]);

$authResponse = curl_exec($curl);
$authError    = curl_error($curl);
$authInfo     = curl_getinfo($curl);

curl_close($curl);

if ($authError) {
    http_response_code(500);
    die(json_encode(["error" => "Auth cURL Error: " . $authError]));
}

if ($authInfo['http_code'] !== 200) {
    http_response_code($authInfo['http_code']);
    die($authResponse);
}

$authData = json_decode($authResponse, true);
if (!is_array($authData) || empty($authData['token'])) {
    http_response_code(500);
    die(json_encode(["error" => "Token not found in auth response", "raw" => $authResponse]));
}

$token = $authData['token'];


// ============ 2. COMPANY SEARCH BY regNo ============
$companySearchUrl = "https://connect.sandbox.creditsafe.com/v1/companies";

$query = [
    "page"      => 1,
    "pageSize"  => 10,
    "countries" => $countries,
    "regNo"     => $regNo,
];

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL            => $companySearchUrl . "?" . http_build_query($query),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST  => "GET",
    CURLOPT_HTTPHEADER     => [
        "Authorization: Bearer " . trim($token),
        "Accept: application/json"
    ],
]);

$companySearchResponse = curl_exec($curl);
$companySearchError    = curl_error($curl);
$companySearchInfo     = curl_getinfo($curl);

curl_close($curl);

if ($companySearchError) {
    http_response_code(500);
    die(json_encode(["error" => "Company Search cURL Error: " . $companySearchError]));
}

if ($companySearchInfo['http_code'] !== 200) {
    http_response_code($companySearchInfo['http_code']);
    die($companySearchResponse);
}

$companySearchData = json_decode($companySearchResponse, true);
if (empty($companySearchData['companies'][0])) {
    http_response_code(404);
    die(json_encode(["error" => "No companies found for regNo {$regNo}", "raw" => $companySearchResponse]));
}

$companyBasic = $companySearchData['companies'][0];

// Dynamic values from first API
$companyId   = $companyBasic['id'];      // e.g. "GB-0-03836192"
$companyName = $companyBasic['name'] ?? null;
$companyReg  = $companyBasic['regNo'] ?? null;


// ============ 3. COMPANY DETAILS BY ID ============
$companyDetailsUrl = "https://connect.sandbox.creditsafe.com/v1/companies/" . urlencode($companyId);

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL            => $companyDetailsUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST  => "GET",
    CURLOPT_HTTPHEADER     => [
        "Authorization: Bearer " . trim($token),
        "Accept: application/json"
    ],
]);

$companyDetailsResponse = curl_exec($curl);
$companyDetailsError    = curl_error($curl);
$companyDetailsInfo     = curl_getinfo($curl);

curl_close($curl);

if ($companyDetailsError) {
    http_response_code(500);
    die(json_encode(["error" => "Company Details cURL Error: " . $companyDetailsError]));
}

if ($companyDetailsInfo['http_code'] !== 200) {
    http_response_code($companyDetailsInfo['http_code']);
    die($companyDetailsResponse);
}

$detailsData = json_decode($companyDetailsResponse, true);
$report      = $detailsData['report'] ?? [];

// ============ 4. EXTRACT IMPORTANT COMPANY DETAILS (3rd API) ============

// 4.1 Company summary / identification
$summary      = $report['companySummary'] ?? [];
$identBasic   = $report['companyIdentification']['basicInformation'] ?? [];
$mainAddress  = $report['contactInformation']['mainAddress'] ?? [];
$websites     = $report['contactInformation']['websites'] ?? [];
$activityMain = $summary['mainActivity'] ?? $identBasic['principalActivity'] ?? [];

// 4.2 Financial snapshot
$turnover     = $summary['latestTurnoverFigure'] ?? [];
$equity       = $summary['latestShareholdersEquityFigure'] ?? [];

// 4.3 Credit score / limit (from creditScore.currentCreditRating)
$creditScoreNode = $report['creditScore']['currentCreditRating'] ?? [];
$creditScoreValue = $creditScoreNode['commonValue'] ?? null;
$creditScoreDesc  = $creditScoreNode['commonDescription'] ?? null;
$creditLimitAmount   = $creditScoreNode['creditLimit']['value']    ?? null;
$creditLimitCurrency = $creditScoreNode['creditLimit']['currency'] ?? null;

// 4.4 Shareholders (flatten basic info only)
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

// 4.5 Directors (limit to first 5 to avoid huge payload)
$directorsRaw = $report['directors']['currentDirectors'] ?? [];
$directors = [];
foreach (array_slice($directorsRaw, 0, 5) as $dir) {
    $directors[] = [
        'name'           => $dir['name'] ?? null,
        'title'          => $dir['title'] ?? null,
        'firstName'      => $dir['firstName'] ?? null,
        'surname'        => $dir['surname'] ?? null,
        'dateOfBirth'    => $dir['dateOfBirth'] ?? null,
        'nationality'    => $dir['nationality'] ?? null,
        'gender'         => $dir['gender'] ?? null,
        'occupation'     => $dir['additionalData']['occupation'] ?? null,
        'presentAppointments' => $dir['additionalData']['presentAppointments'] ?? null,
        'address'        => $dir['address']['simpleValue'] ?? null,
        'position'       => isset($dir['positions'][0]['positionName']) ? $dir['positions'][0]['positionName'] : null,
        'dateAppointed'  => isset($dir['positions'][0]['dateAppointed']) ? $dir['positions'][0]['dateAppointed'] : null,
    ];
}

// 4.6 Group / parent info
$group = $report['groupStructure'] ?? [];
$ultimateParent = $group['ultimateParent'] ?? null;
$immediateParent = $group['immediateParent'] ?? null;

// 4.7 Employee information (latest year only)
$employeesInfo = $report['otherInformation']['employeesInformation'] ?? [];
$latestEmployees = null;
if (!empty($employeesInfo)) {
    // they seem to be ordered latest-first in sample, but we'll just pick first
    $latestEmployees = $employeesInfo[0];
}

// ============ 5. FINAL OUTPUT STRUCTURE ============
$result = [
    'input' => [
        'regNoInput'  => $regNo,
        'countries'   => $countries,
    ],

    'company' => [
        'companyId'                => $summary['companyId'] ?? $companyId,
        'businessName'             => $summary['businessName'] ?? $identBasic['businessName'] ?? $companyName,
        'registeredCompanyName'    => $identBasic['registeredCompanyName'] ?? null,
        'country'                  => $summary['country'] ?? $identBasic['country'] ?? null,
        'companyNumber'            => $summary['companyNumber'] ?? null,
        'companyRegistrationNumber'=> $summary['companyRegistrationNumber'] ?? $identBasic['companyRegistrationNumber'] ?? $companyReg,
        'vatRegistrationNumber'    => $identBasic['vatRegistrationNumber'] ?? null,
        'companyRegistrationDate'  => $identBasic['companyRegistrationDate'] ?? null,
        'legalForm'                => $identBasic['legalForm']['description'] ?? null,
        'ownershipType'            => $identBasic['ownershipType'] ?? null,
        'status'                   => $summary['companyStatus']['description'] 
                                      ?? $identBasic['companyStatus']['description'] 
                                      ?? null,
        'principalActivity'        => $activityMain['description'] ?? null,
        'activityCode'             => $activityMain['code'] ?? null,
        'activityClassification'   => $activityMain['classification'] ?? null,
    ],

    'contact' => [
        'registeredAddress' => [
            'full'       => $mainAddress['simpleValue'] ?? $identBasic['contactAddress']['simpleValue'] ?? null,
            'street'     => $mainAddress['street'] ?? $identBasic['contactAddress']['street'] ?? null,
            'city'       => $mainAddress['city'] ?? $identBasic['contactAddress']['city'] ?? null,
            'postalCode' => $mainAddress['postalCode'] ?? $identBasic['contactAddress']['postalCode'] ?? null,
            'country'    => $mainAddress['country'] ?? $identBasic['country'] ?? null,
        ],
        'telephone' => $mainAddress['telephone'] 
                        ?? $identBasic['contactAddress']['telephone'] 
                        ?? null,
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

    // For debugging / future extension if needed
    //'raw' => $detailsData, // uncomment if you want full blob
];

header('Content-Type: application/json; charset=utf-8');
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

<?php
/**
 * Simple CreditSafe API test script
 * Steps:
 *  1. Authenticate (POST /authenticate)
 *  2. Search company by regNo
 *  3. Fetch company report and print credit limit
 */

$username = 'info@pracoda.com';
$password = '1UxT4XMT1L|5!DVIH!0P1M';
$mode     = 'sandbox'; // change to 'production' if needed
//$regNo    = '08283411';
$regNo    = '00669057';
$baseUrl = ($mode === 'production')
    ? 'https://connect.creditsafe.com/v1'
    : 'https://connect.sandbox.creditsafe.com/v1';

/**
 * Helper to perform cURL requests
 */
function httpRequest($method, $url, $headers = [], $data = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
    }

    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        throw new Exception('cURL error: ' . curl_error($ch));
    }
    curl_close($ch);
    return $response;
}

try {
    // 1. Authenticate
    $authUrl = $baseUrl . '/authenticate';
    $payload = json_encode(['username' => $username, 'password' => $password]);
    $authHeaders = ['Content-Type: application/json'];
    $authResponse = httpRequest('POST', $authUrl, $authHeaders, $payload);
    $authData = json_decode($authResponse, true);

    if (empty($authData['token'])) {
        throw new Exception("Authentication failed: " . $authResponse);
    }
    $token = $authData['token'];
    echo "✅ Authenticated successfully.\n";

    // 2. Search company
    $searchUrl = $baseUrl . '/companies?countries=GB&regNo=' . urlencode($regNo);
    $headers = [
        'Authorization: Bearer ' . $token,
        'Accept: application/json'
    ];
    $searchResponse = httpRequest('GET', $searchUrl, $headers);
    $searchData = json_decode($searchResponse, true);
echo "<pre>";
print_r($searchData);
    $companyId = $searchData['companies'][0]['id'] ?? $searchData['results'][0]['id'] ?? null;
    if (!$companyId) {
        throw new Exception("Company not found: " . $searchResponse);
    }
    echo "✅ Company found, ID: $companyId\n";

    // 3. Get company report
    $reportUrl = $baseUrl . '/companies/' . $companyId;
    $reportResponse = httpRequest('GET', $reportUrl, $headers);
    $reportData = json_decode($reportResponse, true);

    // Extract credit limit
    $creditLimit = $reportData['creditScore']['currentCreditRating']['creditLimit']['value'] ?? null;
    if ($creditLimit) {
        echo "💰 Credit Limit: " . $creditLimit . "\n";
    } else {
        echo "❗ Credit limit not available in response.\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

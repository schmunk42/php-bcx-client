<?php

declare(strict_types=1);

/**
 * Basecamp OAuth 2.0 Flow Example
 *
 * This example demonstrates the complete OAuth flow for Basecamp Classic API.
 *
 * Usage:
 * 1. Register your application at https://launchpad.37signals.com/integrations
 * 2. Set environment variables:
 *    - BASECAMP_CLIENT_ID
 *    - BASECAMP_CLIENT_SECRET
 *    - BASECAMP_REDIRECT_URI
 * 3. Run: php examples/oauth-flow.php
 */

require __DIR__ . '/../vendor/autoload.php';

// Configuration
$clientId = getenv('BASECAMP_CLIENT_ID') ?: 'your-client-id';
$clientSecret = getenv('BASECAMP_CLIENT_SECRET') ?: 'your-client-secret';
$redirectUri = getenv('BASECAMP_REDIRECT_URI') ?: 'http://localhost:8080/callback';

echo "=== Basecamp OAuth 2.0 Flow Example ===\n\n";

// Step 1: Display authorization URL
echo "Step 1: Authorization URL\n";
echo "------------------------\n";
$authUrl = buildAuthorizationUrl($clientId, $redirectUri);
echo "Direct users to this URL:\n";
echo $authUrl . "\n\n";

echo "After authorization, you'll receive a callback with a 'code' parameter.\n";
echo "Example: {$redirectUri}?code=AUTHORIZATION_CODE\n\n";

// For demonstration purposes, prompt for the code
echo "Enter the authorization code from the callback URL (or 'skip' to exit): ";
$code = trim(fgets(STDIN));

if ($code === 'skip' || empty($code)) {
    echo "Exiting demonstration.\n";
    exit(0);
}

echo "\n";

// Step 2: Exchange code for access token
echo "Step 2: Exchange Code for Access Token\n";
echo "--------------------------------------\n";

try {
    $tokenData = exchangeCodeForToken($clientId, $clientSecret, $redirectUri, $code);

    echo "Success! Token obtained:\n";
    echo "  Access Token: " . $tokenData['access_token'] . "\n";
    echo "  Expires In: " . $tokenData['expires_in'] . " seconds (" .
         ($tokenData['expires_in'] / 86400) . " days)\n";
    echo "  Refresh Token: " . $tokenData['refresh_token'] . "\n\n";

    $accessToken = $tokenData['access_token'];

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

// Step 3: Get account information
echo "Step 3: Get Account Information\n";
echo "-------------------------------\n";

try {
    $accountInfo = getAccountInformation($accessToken);

    echo "User Information:\n";
    echo "  ID: " . $accountInfo['identity']['id'] . "\n";
    echo "  Name: " . $accountInfo['identity']['first_name'] . " " .
         $accountInfo['identity']['last_name'] . "\n";
    echo "  Email: " . $accountInfo['identity']['email_address'] . "\n\n";

    echo "Basecamp Classic Accounts:\n";
    foreach ($accountInfo['accounts'] as $account) {
        if ($account['product'] === 'bcx') {
            echo "  - " . $account['name'] . " (ID: " . $account['id'] . ")\n";
            echo "    URL: " . $account['href'] . "\n";
        }
    }
    echo "\n";

    // Get first BCX account
    $accountId = null;
    foreach ($accountInfo['accounts'] as $account) {
        if ($account['product'] === 'bcx') {
            $accountId = (string) $account['id'];
            break;
        }
    }

    if ($accountId === null) {
        echo "No Basecamp Classic accounts found.\n";
        exit(0);
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

// Step 4: Test API with the client
echo "Step 4: Test API Client\n";
echo "-----------------------\n";

use Schmunk42\BasecampApi\Authentication\OAuth2Authentication;
use Schmunk42\BasecampApi\Client\BasecampClient;

try {
    // Calculate expiry
    $expiresAt = (new DateTimeImmutable())
        ->modify(sprintf('+%d seconds', $tokenData['expires_in']));

    // Create authentication
    $auth = new OAuth2Authentication($accessToken, $expiresAt);

    // Create client
    $client = new BasecampClient($accountId, $auth);

    // Test: Get current user
    $me = $client->people()->me();
    echo "Current User (via API):\n";
    echo "  ID: " . $me['id'] . "\n";
    echo "  Name: " . $me['name'] . "\n";
    echo "  Email: " . $me['email_address'] . "\n\n";

    // Test: Get projects
    $projects = $client->projects()->all();
    echo "Projects (" . count($projects) . " total):\n";
    foreach (array_slice($projects, 0, 5) as $project) {
        echo "  - [" . $project['id'] . "] " . $project['name'] . "\n";
    }

    if (count($projects) > 5) {
        echo "  ... and " . (count($projects) - 5) . " more\n";
    }

    echo "\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

// Step 5: Save tokens
echo "Step 5: Save These Values\n";
echo "-------------------------\n";
echo "Add these to your .env file:\n\n";
echo "BASECAMP_ACCOUNT_ID={$accountId}\n";
echo "BASECAMP_ACCESS_TOKEN={$accessToken}\n";
echo "BASECAMP_REFRESH_TOKEN={$tokenData['refresh_token']}\n\n";

echo "Note: The refresh token is used to get a new access token when it expires (after 14 days).\n";
echo "You can now use the basic-usage.php example or your own code.\n";
echo "\nOAuth flow completed successfully!\n";

// =============================================================================
// Helper Functions
// =============================================================================

function buildAuthorizationUrl(string $clientId, string $redirectUri): string
{
    return sprintf(
        'https://launchpad.37signals.com/authorization/new?type=web_server&client_id=%s&redirect_uri=%s',
        urlencode($clientId),
        urlencode($redirectUri)
    );
}

function exchangeCodeForToken(
    string $clientId,
    string $clientSecret,
    string $redirectUri,
    string $code
): array {
    $tokenUrl = 'https://launchpad.37signals.com/authorization/token';

    $data = [
        'type' => 'web_server',
        'client_id' => $clientId,
        'client_secret' => $clientSecret,
        'redirect_uri' => $redirectUri,
        'code' => $code,
    ];

    $ch = curl_init($tokenUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded',
        'User-Agent: php-bcx-client Example (github.com/schmunk42/php-bcx-client)',
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        throw new Exception('cURL Error: ' . $error);
    }

    if ($httpCode !== 200) {
        throw new Exception('Token request failed (HTTP ' . $httpCode . '): ' . $response);
    }

    $tokenData = json_decode($response, true);

    if (!isset($tokenData['access_token'])) {
        throw new Exception('Invalid token response: ' . $response);
    }

    return $tokenData;
}

function getAccountInformation(string $accessToken): array
{
    $url = 'https://launchpad.37signals.com/authorization.json';

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken,
        'User-Agent: php-bcx-client Example (github.com/schmunk42/php-bcx-client)',
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        throw new Exception('cURL Error: ' . $error);
    }

    if ($httpCode !== 200) {
        throw new Exception('Account info request failed (HTTP ' . $httpCode . '): ' . $response);
    }

    $accountInfo = json_decode($response, true);

    if (!isset($accountInfo['accounts'])) {
        throw new Exception('Invalid account info response: ' . $response);
    }

    return $accountInfo;
}

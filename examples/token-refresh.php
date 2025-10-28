<?php

declare(strict_types=1);

/**
 * OAuth Token Refresh Example
 *
 * Demonstrates how to refresh an expired access token using a refresh token.
 *
 * Usage:
 * 1. Set environment variables:
 *    - BASECAMP_CLIENT_ID
 *    - BASECAMP_CLIENT_SECRET
 *    - BASECAMP_REFRESH_TOKEN
 * 2. Run: php examples/token-refresh.php
 */

require __DIR__ . '/../vendor/autoload.php';

// Configuration
$clientId = getenv('BASECAMP_CLIENT_ID') ?: 'your-client-id';
$clientSecret = getenv('BASECAMP_CLIENT_SECRET') ?: 'your-client-secret';
$refreshToken = getenv('BASECAMP_REFRESH_TOKEN') ?: 'your-refresh-token';

echo "=== Basecamp OAuth Token Refresh Example ===\n\n";

if ($clientId === 'your-client-id' || $refreshToken === 'your-refresh-token') {
    echo "Error: Please set the following environment variables:\n";
    echo "  - BASECAMP_CLIENT_ID\n";
    echo "  - BASECAMP_CLIENT_SECRET\n";
    echo "  - BASECAMP_REFRESH_TOKEN\n\n";
    echo "Or edit this script to set them directly.\n";
    exit(1);
}

echo "Refreshing access token...\n\n";

try {
    $tokenData = refreshAccessToken($clientId, $clientSecret, $refreshToken);

    echo "Success! New token obtained:\n";
    echo "  Access Token: " . $tokenData['access_token'] . "\n";
    echo "  Expires In: " . $tokenData['expires_in'] . " seconds (" .
         ($tokenData['expires_in'] / 86400) . " days)\n";
    echo "  Refresh Token: " . $tokenData['refresh_token'] . "\n\n";

    // Calculate expiry date
    $expiresAt = (new DateTimeImmutable())
        ->modify(sprintf('+%d seconds', $tokenData['expires_in']));

    echo "Token Details:\n";
    echo "  Expires At: " . $expiresAt->format('Y-m-d H:i:s T') . "\n";
    echo "  Valid For: " . round($tokenData['expires_in'] / 86400, 1) . " days\n\n";

    echo "Update your .env file with:\n";
    echo "BASECAMP_ACCESS_TOKEN={$tokenData['access_token']}\n";
    echo "BASECAMP_REFRESH_TOKEN={$tokenData['refresh_token']}\n\n";

    // Test the new token
    echo "Testing new token...\n";
    testAccessToken($tokenData['access_token']);

    echo "\nToken refresh completed successfully!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

// =============================================================================
// Helper Functions
// =============================================================================

function refreshAccessToken(string $clientId, string $clientSecret, string $refreshToken): array
{
    $tokenUrl = 'https://launchpad.37signals.com/authorization/token';

    $data = [
        'type' => 'refresh',
        'client_id' => $clientId,
        'client_secret' => $clientSecret,
        'refresh_token' => $refreshToken,
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
        $errorData = json_decode($response, true);
        $errorMessage = $errorData['error'] ?? 'Unknown error';
        throw new Exception('Token refresh failed (HTTP ' . $httpCode . '): ' . $errorMessage);
    }

    $tokenData = json_decode($response, true);

    if (!isset($tokenData['access_token'])) {
        throw new Exception('Invalid token response: ' . $response);
    }

    return $tokenData;
}

function testAccessToken(string $accessToken): void
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
    curl_close($ch);

    if ($httpCode !== 200) {
        throw new Exception('Token test failed (HTTP ' . $httpCode . ')');
    }

    $data = json_decode($response, true);
    echo "  Token is valid!\n";
    echo "  User: " . $data['identity']['first_name'] . " " . $data['identity']['last_name'] . "\n";
    echo "  Email: " . $data['identity']['email_address'] . "\n";
}

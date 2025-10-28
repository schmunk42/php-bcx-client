# OAuth 2.0 Authentication Guide

Complete guide for implementing OAuth 2.0 authentication with the Basecamp Classic API.

## Table of Contents

- [Overview](#overview)
- [OAuth Flow](#oauth-flow)
- [Step-by-Step Implementation](#step-by-step-implementation)
- [Token Management](#token-management)
- [Security Best Practices](#security-best-practices)
- [Troubleshooting](#troubleshooting)

## Overview

Basecamp Classic uses OAuth 2.0 for authentication. This provides:

- **Secure authentication** without exposing user credentials
- **Scoped access** to user data
- **Token expiration** for enhanced security
- **Refresh tokens** for long-lived sessions

### Key Concepts

- **Client ID**: Unique identifier for your application
- **Client Secret**: Secret key for your application (never expose publicly)
- **Authorization Code**: Temporary code exchanged for access token
- **Access Token**: Used to authenticate API requests (expires after 2 weeks)
- **Refresh Token**: Used to obtain new access tokens without user interaction
- **Account ID**: Your Basecamp account identifier (used in API URLs)

## OAuth Flow

```
┌──────────┐                                           ┌──────────┐
│          │                                           │          │
│  User    │                                           │ Basecamp │
│          │                                           │          │
└────┬─────┘                                           └────┬─────┘
     │                                                      │
     │  1. Click "Connect to Basecamp"                     │
     ├─────────────────────────────────────────────────────>
     │                                                      │
     │  2. Redirect to authorization URL                   │
     <─────────────────────────────────────────────────────┤
     │                                                      │
     │  3. User logs in and authorizes                     │
     ├─────────────────────────────────────────────────────>
     │                                                      │
     │  4. Redirect with authorization code                │
     <─────────────────────────────────────────────────────┤
     │                                                      │
┌────┴─────┐                                           ┌────┴─────┐
│          │                                           │          │
│ Your App │                                           │ Basecamp │
│          │                                           │          │
└────┬─────┘                                           └────┬─────┘
     │                                                      │
     │  5. Exchange code for access token                  │
     ├─────────────────────────────────────────────────────>
     │                                                      │
     │  6. Return access token + refresh token             │
     <─────────────────────────────────────────────────────┤
     │                                                      │
     │  7. Make API requests with access token             │
     ├─────────────────────────────────────────────────────>
     │                                                      │
```

## Step-by-Step Implementation

### 1. Register Your Application

Visit: https://launchpad.37signals.com/integrations

**Required Information:**
- **Name**: Your application name (visible to users)
- **Company/Organization**: Your company name
- **Website URL**: Your application's website
- **Redirect URI**: Where users return after authorization
  - Production: `https://yourapp.com/oauth/callback`
  - Development: `http://localhost:8080/oauth/callback`

**After Registration:**
- Save your **Client ID**
- Save your **Client Secret** (keep secure!)

### 2. Build Authorization URL

```php
<?php

$clientId = 'your-client-id';
$redirectUri = 'https://yourapp.com/oauth/callback';

$authorizationUrl = sprintf(
    'https://launchpad.37signals.com/authorization/new?type=web_server&client_id=%s&redirect_uri=%s',
    urlencode($clientId),
    urlencode($redirectUri)
);

// Redirect user to this URL
header('Location: ' . $authorizationUrl);
exit;
```

### 3. Handle Callback

After user authorizes, Basecamp redirects to your `redirect_uri`:

```
https://yourapp.com/oauth/callback?code=abc123def456&state=optional_state
```

### 4. Exchange Code for Access Token

```php
<?php

$clientId = 'your-client-id';
$clientSecret = 'your-client-secret';
$redirectUri = 'https://yourapp.com/oauth/callback';
$code = $_GET['code']; // From callback URL

// Prepare token request
$tokenUrl = 'https://launchpad.37signals.com/authorization/token';
$data = [
    'type' => 'web_server',
    'client_id' => $clientId,
    'client_secret' => $clientSecret,
    'redirect_uri' => $redirectUri,
    'code' => $code,
];

// Make request
$ch = curl_init($tokenUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded',
    'User-Agent: YourApp (yourapp.com)',
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    die('Token request failed: ' . $response);
}

$tokenData = json_decode($response, true);

// Token data:
// {
//   "access_token": "BAhbByIBsHsidmVyc2lvbiI6MSwidXNlcl9pZCI...",
//   "expires_in": 1209600,  // 2 weeks in seconds
//   "refresh_token": "refresh-token-here"
// }

$accessToken = $tokenData['access_token'];
$refreshToken = $tokenData['refresh_token'];
$expiresIn = $tokenData['expires_in'];

// Store these securely (database, session, etc.)
```

### 5. Get Account Information

```php
<?php

$accessToken = 'your-access-token';

$ch = curl_init('https://launchpad.37signals.com/authorization.json');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken,
    'User-Agent: YourApp (yourapp.com)',
]);

$response = curl_exec($ch);
curl_close($ch);

$accounts = json_decode($response, true);

// Response:
// {
//   "identity": {
//     "id": 12345678,
//     "email": "user@example.com",
//     "first_name": "John",
//     "last_name": "Doe"
//   },
//   "accounts": [
//     {
//       "product": "bcx",
//       "id": 999999999,
//       "name": "Your Company",
//       "href": "https://basecamp.com/999999999/api/v1"
//     }
//   ]
// }

foreach ($accounts['accounts'] as $account) {
    if ($account['product'] === 'bcx') {
        $accountId = $account['id'];
        // Use this account ID with the API client
        break;
    }
}
```

### 6. Use with API Client

```php
<?php

use Schmunk42\BasecampApi\Authentication\OAuth2Authentication;
use Schmunk42\BasecampApi\Client\BasecampClient;

$accessToken = 'your-access-token';
$accountId = '999999999';

// Calculate expiry date (2 weeks from now)
$expiresAt = (new DateTimeImmutable())->modify('+14 days');

// Create authentication
$auth = new OAuth2Authentication($accessToken, $expiresAt);

// Create client
$client = new BasecampClient($accountId, $auth);

// Make API calls
$projects = $client->projects()->all();
```

## Token Management

### Storing Tokens Securely

**Database Example:**

```sql
CREATE TABLE oauth_tokens (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    access_token TEXT NOT NULL,
    refresh_token TEXT NOT NULL,
    expires_at DATETIME NOT NULL,
    account_id VARCHAR(50) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_user_id (user_id),
    INDEX idx_expires_at (expires_at)
);
```

### Refreshing Access Tokens

Access tokens expire after 2 weeks. Refresh them before expiration:

```php
<?php

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

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        throw new Exception('Token refresh failed: ' . $response);
    }

    return json_decode($response, true);
}

// Usage
$tokenData = refreshAccessToken($clientId, $clientSecret, $refreshToken);
$newAccessToken = $tokenData['access_token'];
$newRefreshToken = $tokenData['refresh_token']; // May be the same or new

// Update stored tokens
```

### Automatic Token Refresh

```php
<?php

class TokenManager
{
    private string $clientId;
    private string $clientSecret;
    private string $accessToken;
    private string $refreshToken;
    private DateTimeImmutable $expiresAt;

    public function getValidAccessToken(): string
    {
        // Refresh if expires within 1 hour
        $oneHourFromNow = (new DateTimeImmutable())->modify('+1 hour');

        if ($this->expiresAt < $oneHourFromNow) {
            $this->refreshToken();
        }

        return $this->accessToken;
    }

    private function refreshToken(): void
    {
        $tokenData = $this->requestTokenRefresh();

        $this->accessToken = $tokenData['access_token'];
        $this->refreshToken = $tokenData['refresh_token'];
        $this->expiresAt = (new DateTimeImmutable())
            ->modify(sprintf('+%d seconds', $tokenData['expires_in']));

        // Save to database
        $this->saveToDatabase();
    }

    private function requestTokenRefresh(): array
    {
        // Implementation from refreshAccessToken() above
    }

    private function saveToDatabase(): void
    {
        // Save tokens to database
    }
}
```

## Security Best Practices

### 1. Protect Client Secret

**DO:**
- Store in environment variables
- Keep in secure server-side configuration
- Use secrets management services (AWS Secrets Manager, HashiCorp Vault)

**DON'T:**
- Commit to version control
- Expose in client-side code
- Share in public documentation

### 2. Validate Redirect URI

```php
<?php

$allowedRedirectUris = [
    'https://yourapp.com/oauth/callback',
    'http://localhost:8080/oauth/callback', // Development only
];

$redirectUri = $_GET['redirect_uri'] ?? '';

if (!in_array($redirectUri, $allowedRedirectUris, true)) {
    die('Invalid redirect URI');
}
```

### 3. Use State Parameter

Prevent CSRF attacks:

```php
<?php

// When building authorization URL
session_start();
$state = bin2hex(random_bytes(32));
$_SESSION['oauth_state'] = $state;

$authUrl = sprintf(
    'https://launchpad.37signals.com/authorization/new?type=web_server&client_id=%s&redirect_uri=%s&state=%s',
    urlencode($clientId),
    urlencode($redirectUri),
    urlencode($state)
);

// When handling callback
session_start();
$receivedState = $_GET['state'] ?? '';
$expectedState = $_SESSION['oauth_state'] ?? '';

if (!hash_equals($expectedState, $receivedState)) {
    die('Invalid state parameter - possible CSRF attack');
}

unset($_SESSION['oauth_state']);
```

### 4. Encrypt Stored Tokens

```php
<?php

function encryptToken(string $token, string $key): string
{
    $cipher = 'aes-256-gcm';
    $ivLength = openssl_cipher_iv_length($cipher);
    $iv = random_bytes($ivLength);

    $encrypted = openssl_encrypt($token, $cipher, $key, 0, $iv, $tag);

    return base64_encode($iv . $tag . $encrypted);
}

function decryptToken(string $encryptedToken, string $key): string
{
    $cipher = 'aes-256-gcm';
    $decoded = base64_decode($encryptedToken);

    $ivLength = openssl_cipher_iv_length($cipher);
    $iv = substr($decoded, 0, $ivLength);
    $tag = substr($decoded, $ivLength, 16);
    $encrypted = substr($decoded, $ivLength + 16);

    return openssl_decrypt($encrypted, $cipher, $key, 0, $iv, $tag);
}
```

### 5. Rate Limiting

Implement rate limiting for OAuth endpoints to prevent abuse:

```php
<?php

// Example: Max 10 token requests per hour per IP
$key = 'oauth_rate_limit:' . $_SERVER['REMOTE_ADDR'];
$requests = $redis->incr($key);

if ($requests === 1) {
    $redis->expire($key, 3600); // 1 hour
}

if ($requests > 10) {
    http_response_code(429);
    die('Rate limit exceeded. Try again later.');
}
```

## Troubleshooting

### Common Errors

#### "invalid_grant" Error

**Causes:**
- Authorization code already used
- Code expired (10 minutes)
- Mismatched redirect URI

**Solution:**
- Start OAuth flow again
- Ensure redirect URI matches exactly

#### "unauthorized_client" Error

**Causes:**
- Invalid client ID or secret
- Application not registered

**Solution:**
- Verify credentials in Launchpad
- Check for typos

#### "invalid_request" Error

**Causes:**
- Missing required parameters
- Malformed request

**Solution:**
- Check all required fields are present
- Verify parameter formatting

### Testing OAuth Flow

Use this script to test the complete flow:

```bash
# See examples/oauth-flow.php
php examples/oauth-flow.php
```

### Debug Logging

```php
<?php

function logOAuthRequest(string $stage, array $data): void
{
    error_log(sprintf(
        '[OAuth:%s] %s',
        $stage,
        json_encode($data, JSON_PRETTY_PRINT)
    ));
}

// Usage
logOAuthRequest('authorization_start', [
    'client_id' => $clientId,
    'redirect_uri' => $redirectUri,
]);
```

## Resources

- [Basecamp OAuth Documentation](https://github.com/basecamp/api/blob/master/sections/authentication.md)
- [OAuth 2.0 RFC 6749](https://tools.ietf.org/html/rfc6749)
- [OAuth 2.0 Security Best Practices](https://tools.ietf.org/html/draft-ietf-oauth-security-topics)
- [Basecamp API Reference](https://github.com/basecamp/bcx-api)

## Example Implementation

See [examples/oauth-flow.php](../examples/oauth-flow.php) for a complete working example.

# OAuth 2.0 Code Examples

Complete code examples for implementing OAuth 2.0 authentication with the Basecamp Classic API.

## Table of Contents

- [Authorization URL](#authorization-url)
- [Token Exchange](#token-exchange)
- [Token Refresh](#token-refresh)
- [Get Account Information](#get-account-information)
- [Using with API Client](#using-with-api-client)
- [Token Management](#token-management)
- [Security Examples](#security-examples)

---

## Authorization URL

### Build Basic Authorization URL

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

### With State Parameter (CSRF Protection)

```php
<?php

session_start();

$clientId = 'your-client-id';
$redirectUri = 'https://yourapp.com/oauth/callback';

// Generate state for CSRF protection
$state = bin2hex(random_bytes(32));
$_SESSION['oauth_state'] = $state;

$authorizationUrl = sprintf(
    'https://launchpad.37signals.com/authorization/new?type=web_server&client_id=%s&redirect_uri=%s&state=%s',
    urlencode($clientId),
    urlencode($redirectUri),
    urlencode($state)
);

header('Location: ' . $authorizationUrl);
exit;
```

### Using cURL (for testing)

```bash
# Build the URL
AUTH_URL="https://launchpad.37signals.com/authorization/new"
AUTH_URL="${AUTH_URL}?type=web_server"
AUTH_URL="${AUTH_URL}&client_id=YOUR_CLIENT_ID"
AUTH_URL="${AUTH_URL}&redirect_uri=http%3A%2F%2Flocalhost%3A8080%2Fcallback"

echo "Open this URL in your browser:"
echo $AUTH_URL
```

---

## Token Exchange

### Exchange Authorization Code for Access Token

```php
<?php

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
        'User-Agent: MyApp/1.0',
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        throw new Exception('Token exchange failed: ' . $response);
    }

    return json_decode($response, true);
}

// Usage
$tokenData = exchangeCodeForToken($clientId, $clientSecret, $redirectUri, $code);
$accessToken = $tokenData['access_token'];
$refreshToken = $tokenData['refresh_token'];
$expiresIn = $tokenData['expires_in']; // 1209600 seconds (14 days)
```

### With State Validation

```php
<?php

session_start();

// Validate state parameter
$receivedState = $_GET['state'] ?? '';
$expectedState = $_SESSION['oauth_state'] ?? '';

if (!hash_equals($expectedState, $receivedState)) {
    die('Invalid state parameter - possible CSRF attack');
}

unset($_SESSION['oauth_state']);

// Exchange code for token
$code = $_GET['code'] ?? '';
$tokenData = exchangeCodeForToken($clientId, $clientSecret, $redirectUri, $code);

// Store tokens securely
$_SESSION['access_token'] = $tokenData['access_token'];
$_SESSION['refresh_token'] = $tokenData['refresh_token'];
$_SESSION['expires_at'] = time() + $tokenData['expires_in'];
```

### Using cURL (Command Line)

```bash
curl -X POST https://launchpad.37signals.com/authorization/token \
  -d "type=web_server" \
  -d "client_id=YOUR_CLIENT_ID" \
  -d "client_secret=YOUR_CLIENT_SECRET" \
  -d "redirect_uri=http://localhost:8080/callback" \
  -d "code=AUTHORIZATION_CODE"
```

---

## Token Refresh

### Refresh Expired Access Token

```php
<?php

function refreshAccessToken(
    string $clientId,
    string $clientSecret,
    string $refreshToken
): array {
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
$newRefreshToken = $tokenData['refresh_token'];
```

### Using cURL (Command Line)

```bash
curl -X POST https://launchpad.37signals.com/authorization/token \
  -d "type=refresh" \
  -d "client_id=YOUR_CLIENT_ID" \
  -d "client_secret=YOUR_CLIENT_SECRET" \
  -d "refresh_token=YOUR_REFRESH_TOKEN"
```

---

## Get Account Information

### Retrieve User and Account Details

```php
<?php

function getAccountInformation(string $accessToken): array
{
    $url = 'https://launchpad.37signals.com/authorization.json';

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken,
        'User-Agent: MyApp/1.0',
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        throw new Exception('Failed to get account info: ' . $response);
    }

    return json_decode($response, true);
}

// Usage
$accountInfo = getAccountInformation($accessToken);

// Extract user information
$userId = $accountInfo['identity']['id'];
$userEmail = $accountInfo['identity']['email_address'];
$userName = $accountInfo['identity']['first_name'] . ' ' . $accountInfo['identity']['last_name'];

// Find Basecamp Classic accounts
$bcxAccounts = array_filter($accountInfo['accounts'], function($account) {
    return $account['product'] === 'bcx';
});

foreach ($bcxAccounts as $account) {
    echo "Account: {$account['name']} (ID: {$account['id']})\n";
}
```

### Using cURL (Command Line)

```bash
curl -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  https://launchpad.37signals.com/authorization.json
```

---

## Using with API Client

### Basic Setup

```php
<?php

require 'vendor/autoload.php';

use Schmunk42\BasecampApi\Authentication\OAuth2Authentication;
use Schmunk42\BasecampApi\Client\BasecampClient;

// Create authentication with token
$auth = new OAuth2Authentication($accessToken);

// Create client with account ID
$client = new BasecampClient($accountId, $auth);

// Make API calls
$projects = $client->projects()->all();
```

### With Token Expiry Tracking

```php
<?php

use Schmunk42\BasecampApi\Authentication\OAuth2Authentication;
use Schmunk42\BasecampApi\Client\BasecampClient;

// Calculate expiry time
$expiresAt = (new DateTimeImmutable())
    ->modify('+14 days'); // or use $tokenData['expires_in']

// Create authentication with expiry
$auth = new OAuth2Authentication($accessToken, $expiresAt);

// Check if token is still valid
if (!$auth->isValid()) {
    // Token expired - refresh it
    $tokenData = refreshAccessToken($clientId, $clientSecret, $refreshToken);
    $auth = new OAuth2Authentication(
        $tokenData['access_token'],
        (new DateTimeImmutable())->modify('+14 days')
    );
}

$client = new BasecampClient($accountId, $auth);
```

### With Logging

```php
<?php

use Schmunk42\BasecampApi\Authentication\OAuth2Authentication;
use Schmunk42\BasecampApi\Client\BasecampClient;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// Create logger
$logger = new Logger('basecamp');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));

// Create client with logger
$auth = new OAuth2Authentication($accessToken);
$client = new BasecampClient($accountId, $auth, null, $logger);

// API calls will be logged
$projects = $client->projects()->all();
```

---

## Token Management

### Store Tokens in Database

```php
<?php

function storeTokens(int $userId, array $tokenData): void
{
    $pdo = new PDO('mysql:host=localhost;dbname=myapp', 'user', 'pass');

    $stmt = $pdo->prepare('
        INSERT INTO oauth_tokens (user_id, access_token, refresh_token, expires_at)
        VALUES (:user_id, :access_token, :refresh_token, :expires_at)
        ON DUPLICATE KEY UPDATE
            access_token = :access_token,
            refresh_token = :refresh_token,
            expires_at = :expires_at
    ');

    $stmt->execute([
        'user_id' => $userId,
        'access_token' => $tokenData['access_token'],
        'refresh_token' => $tokenData['refresh_token'],
        'expires_at' => date('Y-m-d H:i:s', time() + $tokenData['expires_in']),
    ]);
}

function getValidAccessToken(int $userId): string
{
    $pdo = new PDO('mysql:host=localhost;dbname=myapp', 'user', 'pass');

    $stmt = $pdo->prepare('
        SELECT access_token, refresh_token, expires_at
        FROM oauth_tokens
        WHERE user_id = :user_id
    ');

    $stmt->execute(['user_id' => $userId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if token is expired
    $expiresAt = strtotime($row['expires_at']);
    if ($expiresAt < time()) {
        // Refresh the token
        $tokenData = refreshAccessToken(
            getenv('BASECAMP_CLIENT_ID'),
            getenv('BASECAMP_CLIENT_SECRET'),
            $row['refresh_token']
        );

        // Store new tokens
        storeTokens($userId, $tokenData);

        return $tokenData['access_token'];
    }

    return $row['access_token'];
}
```

### Automatic Token Refresh Class

```php
<?php

class TokenManager
{
    private string $clientId;
    private string $clientSecret;
    private string $accessToken;
    private string $refreshToken;
    private DateTimeImmutable $expiresAt;

    public function __construct(
        string $clientId,
        string $clientSecret,
        string $accessToken,
        string $refreshToken,
        DateTimeImmutable $expiresAt
    ) {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;
        $this->expiresAt = $expiresAt;
    }

    public function getValidAccessToken(): string
    {
        // Refresh if expires within 1 hour
        $oneHourFromNow = (new DateTimeImmutable())->modify('+1 hour');

        if ($this->expiresAt < $oneHourFromNow) {
            $this->refreshTokens();
        }

        return $this->accessToken;
    }

    private function refreshTokens(): void
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
        $tokenUrl = 'https://launchpad.37signals.com/authorization/token';

        $data = [
            'type' => 'refresh',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'refresh_token' => $this->refreshToken,
        ];

        $ch = curl_init($tokenUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    private function saveToDatabase(): void
    {
        // Implement database save logic
    }
}
```

---

## Security Examples

### Encrypt Tokens Before Storing

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

// Usage
$encryptionKey = getenv('ENCRYPTION_KEY'); // Store securely
$encryptedToken = encryptToken($accessToken, $encryptionKey);
// Store $encryptedToken in database

// Later, retrieve and decrypt
$decryptedToken = decryptToken($encryptedToken, $encryptionKey);
```

### Rate Limiting OAuth Requests

```php
<?php

function checkRateLimit(string $identifier): void
{
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);

    $key = 'oauth_rate_limit:' . $identifier;
    $requests = $redis->incr($key);

    if ($requests === 1) {
        $redis->expire($key, 3600); // 1 hour
    }

    if ($requests > 10) {
        http_response_code(429);
        die('Rate limit exceeded. Try again later.');
    }
}

// Usage - before making OAuth requests
$identifier = $_SERVER['REMOTE_ADDR']; // or user ID
checkRateLimit($identifier);
```

### Validate Redirect URI

```php
<?php

function validateRedirectUri(string $redirectUri): bool
{
    $allowedUris = [
        'https://myapp.com/oauth/callback',
        'http://localhost:8080/callback', // Development only
    ];

    return in_array($redirectUri, $allowedUris, true);
}

// Usage
$redirectUri = $_GET['redirect_uri'] ?? '';

if (!validateRedirectUri($redirectUri)) {
    http_response_code(400);
    die('Invalid redirect URI');
}
```

---

## Complete OAuth Flow Example

```php
<?php

// Step 1: Redirect to authorization
session_start();

if (!isset($_GET['code'])) {
    $clientId = getenv('BASECAMP_CLIENT_ID');
    $redirectUri = 'https://myapp.com/oauth/callback';
    $state = bin2hex(random_bytes(32));

    $_SESSION['oauth_state'] = $state;

    $authUrl = sprintf(
        'https://launchpad.37signals.com/authorization/new?type=web_server&client_id=%s&redirect_uri=%s&state=%s',
        urlencode($clientId),
        urlencode($redirectUri),
        urlencode($state)
    );

    header('Location: ' . $authUrl);
    exit;
}

// Step 2: Handle callback
$receivedState = $_GET['state'] ?? '';
$expectedState = $_SESSION['oauth_state'] ?? '';

if (!hash_equals($expectedState, $receivedState)) {
    die('Invalid state parameter');
}

$code = $_GET['code'];

// Step 3: Exchange code for token
$tokenData = exchangeCodeForToken(
    getenv('BASECAMP_CLIENT_ID'),
    getenv('BASECAMP_CLIENT_SECRET'),
    'https://myapp.com/oauth/callback',
    $code
);

// Step 4: Get account information
$accountInfo = getAccountInformation($tokenData['access_token']);

// Step 5: Store tokens
$userId = $accountInfo['identity']['id'];
storeTokens($userId, $tokenData);

// Step 6: Redirect to app
header('Location: /dashboard');
exit;
```

---

## Resources

- [Main OAuth Guide](./OAUTH.md) - Step-by-step setup guide
- [Basecamp OAuth Documentation](https://github.com/basecamp/api/blob/master/sections/authentication.md)
- [OAuth 2.0 RFC 6749](https://tools.ietf.org/html/rfc6749)
- [Basecamp Classic API Reference](https://github.com/basecamp/bcx-api)

# OAuth 2.0 Quick Reference

Quick reference card for Basecamp Classic OAuth implementation.

## URLs

| Purpose | URL |
|---------|-----|
| Register App | https://launchpad.37signals.com/integrations |
| Authorization | https://launchpad.37signals.com/authorization/new |
| Token Exchange | https://launchpad.37signals.com/authorization/token |
| Account Info | https://launchpad.37signals.com/authorization.json |

## OAuth Flow

```
1. Register App → Get Client ID + Client Secret
2. Authorization URL → User approves
3. Callback → Receive authorization code
4. Exchange code → Get access token + refresh token
5. Get accounts → Find Account ID
6. Use API → Make requests with access token
```

## Authorization URL

```
https://launchpad.37signals.com/authorization/new?type=web_server&client_id=CLIENT_ID&redirect_uri=REDIRECT_URI&state=STATE
```

**Parameters:**
- `type`: Always `web_server`
- `client_id`: Your application's client ID
- `redirect_uri`: Your registered callback URL (must match exactly)
- `state`: Random string for CSRF protection (recommended)

## Token Exchange

**Request:**
```bash
curl -X POST https://launchpad.37signals.com/authorization/token \
  -d "type=web_server" \
  -d "client_id=CLIENT_ID" \
  -d "client_secret=CLIENT_SECRET" \
  -d "redirect_uri=REDIRECT_URI" \
  -d "code=AUTHORIZATION_CODE"
```

**Response:**
```json
{
  "access_token": "BAh...",
  "expires_in": 1209600,
  "refresh_token": "abc123..."
}
```

## Token Refresh

**Request:**
```bash
curl -X POST https://launchpad.37signals.com/authorization/token \
  -d "type=refresh" \
  -d "client_id=CLIENT_ID" \
  -d "client_secret=CLIENT_SECRET" \
  -d "refresh_token=REFRESH_TOKEN"
```

**Response:**
```json
{
  "access_token": "NEW_TOKEN",
  "expires_in": 1209600,
  "refresh_token": "NEW_REFRESH_TOKEN"
}
```

## Get Account Info

**Request:**
```bash
curl -H "Authorization: Bearer ACCESS_TOKEN" \
  https://launchpad.37signals.com/authorization.json
```

**Response:**
```json
{
  "identity": {
    "id": 12345,
    "email_address": "user@example.com",
    "first_name": "John",
    "last_name": "Doe"
  },
  "accounts": [
    {
      "product": "bcx",
      "id": 999999999,
      "name": "Company Name",
      "href": "https://basecamp.com/999999999/api/v1"
    }
  ]
}
```

## PHP Code Snippets

### Build Authorization URL

```php
$authUrl = sprintf(
    'https://launchpad.37signals.com/authorization/new?type=web_server&client_id=%s&redirect_uri=%s&state=%s',
    urlencode($clientId),
    urlencode($redirectUri),
    urlencode($state)
);
```

### Exchange Code for Token

```php
$ch = curl_init('https://launchpad.37signals.com/authorization/token');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'type' => 'web_server',
    'client_id' => $clientId,
    'client_secret' => $clientSecret,
    'redirect_uri' => $redirectUri,
    'code' => $code,
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$tokenData = json_decode($response, true);
```

### Use with API Client

```php
use Schmunk42\BasecampApi\Authentication\OAuth2Authentication;
use Schmunk42\BasecampApi\Client\BasecampClient;

$auth = new OAuth2Authentication(
    $accessToken,
    (new DateTimeImmutable())->modify('+14 days')
);

$client = new BasecampClient($accountId, $auth);
$projects = $client->projects()->all();
```

## Important Values

| Item | Value | Notes |
|------|-------|-------|
| Token Lifetime | 1,209,600 seconds | 14 days / 2 weeks |
| Code Expiry | 600 seconds | 10 minutes |
| Product Type | `bcx` | Basecamp Classic identifier |

## Common Errors

| Error | Cause | Solution |
|-------|-------|----------|
| `invalid_grant` | Code used/expired | Start flow again |
| `unauthorized_client` | Wrong credentials | Check Client ID/Secret |
| `invalid_request` | Missing parameter | Verify all fields |
| `redirect_uri_mismatch` | URI doesn't match | Use exact registered URI |

## Security Checklist

- [ ] Use HTTPS for redirect URIs (production)
- [ ] Implement state parameter (CSRF protection)
- [ ] Validate state on callback
- [ ] Store Client Secret securely (never in code)
- [ ] Encrypt tokens in database
- [ ] Implement rate limiting
- [ ] Use refresh tokens for long-lived access
- [ ] Handle token expiration gracefully

## Example Scripts

### With Docker (Recommended)

```bash
# On Host

# Complete OAuth flow (get access token)
make oauth-flow

# Token refresh
make token-refresh

# Or directly
docker compose run --rm php php examples/oauth-flow.php
docker compose run --rm php php examples/token-refresh.php

# Or in shell
make shell
# In Container
php examples/oauth-flow.php
php examples/token-refresh.php
```

### Native PHP

```bash
# On Host

# Complete OAuth flow
export BASECAMP_CLIENT_ID="your-client-id"
export BASECAMP_CLIENT_SECRET="your-client-secret"
php examples/oauth-flow.php

# Token refresh
export BASECAMP_REFRESH_TOKEN="your-refresh-token"
php examples/token-refresh.php
```

## Environment Variables

```bash
# For development/testing
BASECAMP_CLIENT_ID=your-client-id
BASECAMP_CLIENT_SECRET=your-client-secret
BASECAMP_REDIRECT_URI=http://localhost:8080/callback
BASECAMP_ACCOUNT_ID=999999999
BASECAMP_ACCESS_TOKEN=your-access-token
BASECAMP_REFRESH_TOKEN=your-refresh-token
```

## Resources

- [Complete OAuth Guide](./OAUTH.md)
- [Basecamp OAuth Docs](https://github.com/basecamp/api/blob/master/sections/authentication.md)
- [OAuth 2.0 RFC](https://tools.ietf.org/html/rfc6749)

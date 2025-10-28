# Setting Up a Basecamp 2 API Client

Complete step-by-step guide to set up OAuth 2.0 authentication for the Basecamp 2 (BCX) API.

## Overview

This guide walks you through setting up a Basecamp 2 API client from registration to making your first API call. The process takes approximately 10-15 minutes.

### What You'll Need

- A Basecamp 2 account
- Basic command line knowledge
- Docker installed (recommended) OR PHP 8.4+ installed locally

### What You'll Get

- OAuth 2.0 credentials (Client ID & Secret)
- Access token for API requests
- Account ID for your Basecamp account
- Working API client ready to use

---

## Step 1: Register Your Application

### 1.1 Go to Basecamp Launchpad

Visit: **https://launchpad.37signals.com/integrations**

Log in with your Basecamp account credentials.

### 1.2 Create New Application

Click **"Register another application"**

### 1.3 Fill in Application Details

| Field | What to Enter | Example |
|-------|---------------|---------|
| **Name** | Your application name (visible to users) | "My Company BCX Integration" |
| **Company** | Your organization name | "ACME Corp" |
| **Website** | Your website or app URL | "https://mycompany.com" |
| **Redirect URI** | OAuth callback URL | Development: `http://localhost:8080/callback`<br>Production: `https://myapp.com/oauth/callback` |

**Important Notes:**
- The Redirect URI must match **exactly** when making OAuth requests
- For local testing, use `http://localhost:8080/callback`
- For production, use HTTPS

### 1.4 Save Your Credentials

After registration, you'll receive:

- **Client ID**: `532cdd5d1492b2fb99182070c08519d353017ec9` (example)
- **Client Secret**: `a8f3d9e2b7c4... ` (example - keep this secret!)

**⚠️ Important:** Store these securely. Never commit Client Secret to version control.

---

## Step 2: Set Up Your Development Environment

### Option A: Using Docker (Recommended)

```bash
# On Host

# Clone the repository
git clone https://github.com/schmunk42/php-bcx-client.git
cd php-bcx-client

# Copy environment template
cp .env.example .env

# Edit .env and add your credentials
nano .env
```

Add these lines to `.env`:
```env
BASECAMP_CLIENT_ID=your-client-id-from-step-1
BASECAMP_CLIENT_SECRET=your-client-secret-from-step-1
BASECAMP_REDIRECT_URI=http://localhost:8080/callback
```

### Option B: Using Native PHP

```bash
# On Host

# Install via Composer
composer require schmunk42/php-bcx-client

# Set environment variables
export BASECAMP_CLIENT_ID="your-client-id"
export BASECAMP_CLIENT_SECRET="your-client-secret"
export BASECAMP_REDIRECT_URI="http://localhost:8080/callback"
```

---

## Step 3: Get Your OAuth Access Token

### 3.1 Run the OAuth Flow Script

**With Docker:**
```bash
# On Host
make oauth-flow
```

**With Native PHP:**
```bash
# On Host
php vendor/schmunk42/php-bcx-client/examples/oauth-flow.php
```

### 3.2 Authorize Your Application

The script will display an authorization URL:

```
Step 1: Authorization URL
------------------------
Direct users to this URL:
https://launchpad.37signals.com/authorization/new?type=web_server&client_id=...
```

**Actions:**
1. **Copy the URL** and open it in your browser
2. **Log in** to Basecamp if prompted
3. **Click "Yes, I'll allow access"** to authorize your application
4. You'll be redirected to your callback URL

### 3.3 Get the Authorization Code

After authorization, you'll be redirected to:
```
http://localhost:8080/callback?code=29a0307c1234567890abcdef
```

**Extract the code:** The part after `code=` is your authorization code (e.g., `29a0307c1234567890abcdef`)

**Note:** If you see a "This site can't be reached" error, that's normal! Just copy the code from the URL bar.

### 3.4 Exchange Code for Token

Paste the authorization code back into the terminal when prompted:

```
Enter the authorization code from the callback URL (or 'skip' to exit): 29a0307c1234567890abcdef
```

The script will exchange the code for an access token and display:

```
Step 2: Exchange Code for Access Token
--------------------------------------
Success! Token obtained:
  Access Token: BAhbB0kiAbB7ImNsaWVu...
  Expires In: 1209600 seconds (14 days)
  Refresh Token: BAhbB0kiAbB7ImNsaWVu...
```

---

## Step 4: Get Your Account Information

The script automatically retrieves your account information:

```
Step 3: Get Account Information
-------------------------------
User Information:
  ID: 798629
  Name: John Doe
  Email: john@example.com

Basecamp 2 Accounts:
  - ACME Corp (ID: 1757700)
    URL: https://basecamp.com/1757700/api/v1
```

**Save the Account ID** - you'll need this for API requests (e.g., `1757700`)

---

## Step 5: Test the API Client

The script will test the API connection:

```
Step 4: Test API Client
-----------------------
Current User (via API):
  ID: 42247
  Name: John Doe
  Email: john@example.com

Projects (64 total):
  - [18909015] Project Alpha
  - [16276980] Project Beta
  - [3862833] Project Gamma
  ... and 61 more
```

✅ **Success!** Your API client is working correctly.

---

## Step 6: Save Your Credentials

The script outputs all credentials needed:

```
Step 5: Save These Values
-------------------------
Add these to your .env file:

BASECAMP_ACCOUNT_ID=1757700
BASECAMP_ACCESS_TOKEN=BAhbB0kiAbB7ImNsaWVu...
BASECAMP_REFRESH_TOKEN=BAhbB0kiAbB7ImNsaWVu...
```

### 6.1 Update Your .env File

```bash
# On Host
nano .env
```

Add or update these values:
```env
# OAuth Credentials (from Step 1)
BASECAMP_CLIENT_ID=532cdd5d1492b2fb99182070c08519d353017ec9
BASECAMP_CLIENT_SECRET=a8f3d9e2b7c4...
BASECAMP_REDIRECT_URI=http://localhost:8080/callback

# API Credentials (from OAuth flow)
BASECAMP_ACCOUNT_ID=1757700
BASECAMP_ACCESS_TOKEN=BAhbB0kiAbB7ImNsaWVu...
BASECAMP_REFRESH_TOKEN=BAhbB0kiAbB7ImNsaWVu...
```

### 6.2 Security Check

✅ Ensure `.env` is in your `.gitignore` (already configured)
✅ Never commit `.env` to version control
✅ Keep Client Secret and tokens secure

---

## Step 7: Use the API Client

### 7.1 Basic Usage Example

**With Docker:**
```bash
# On Host
make example
```

**With Native PHP:**
```bash
# On Host
php vendor/schmunk42/php-bcx-client/examples/basic-usage.php
```

### 7.2 In Your Own Code

```php
<?php

require 'vendor/autoload.php';

use Schmunk42\BasecampApi\Authentication\OAuth2Authentication;
use Schmunk42\BasecampApi\Client\BasecampClient;

// Load credentials from environment or .env
$accountId = getenv('BASECAMP_ACCOUNT_ID');
$accessToken = getenv('BASECAMP_ACCESS_TOKEN');

// Create authenticated client
$auth = new OAuth2Authentication($accessToken);
$client = new BasecampClient($accountId, $auth);

// Get all projects
$projects = $client->projects()->all();

foreach ($projects as $project) {
    echo sprintf("[%d] %s\n", $project['id'], $project['name']);
}

// Get current user
$me = $client->people()->me();
echo "Logged in as: {$me['name']}\n";

// Get todolists for a project
$todolists = $client->todolists()->all($projectId);

// Get todos in a todolist
$todos = $client->todos()->all($projectId, $todolistId);
```

See [OAUTH-EXAMPLES.md](./OAUTH-EXAMPLES.md) for more code examples.

---

## Step 8: Token Refresh (When Tokens Expire)

Access tokens expire after **14 days**. Use the refresh token to get a new one:

### 8.1 Run Token Refresh

**With Docker:**
```bash
# On Host
make token-refresh
```

**With Native PHP:**
```bash
# On Host
export BASECAMP_REFRESH_TOKEN="your-refresh-token"
php vendor/schmunk42/php-bcx-client/examples/token-refresh.php
```

### 8.2 Update .env with New Token

The script outputs:
```
Success! New token obtained:
  Access Token: BAhbB0kiAbB7NEW_TOKEN...
  Expires In: 1209600 seconds (14 days)
  Refresh Token: BAhbB0kiAbB7NEW_REFRESH...
```

Update your `.env` file with the new tokens.

---

## Quick Reference

### OAuth Endpoints

| Purpose | URL |
|---------|-----|
| Register App | https://launchpad.37signals.com/integrations |
| Authorization | https://launchpad.37signals.com/authorization/new |
| Token Exchange | https://launchpad.37signals.com/authorization/token |
| Account Info | https://launchpad.37signals.com/authorization.json |
| API Base | https://basecamp.com/{ACCOUNT_ID}/api/v1 |

### Token Lifetimes

| Token Type | Lifetime | Renewable |
|------------|----------|-----------|
| Authorization Code | 10 minutes | No - use immediately |
| Access Token | 14 days (1,209,600 seconds) | Yes - with refresh token |
| Refresh Token | Indefinite | Yes - returns new token |

### Common Docker Commands

```bash
make oauth-flow       # Get OAuth access token
make token-refresh    # Refresh expired token
make example          # Run basic usage example
make shell            # Open container shell
make test             # Run tests
make help             # Show all commands
```

### Environment Variables

```env
# OAuth Setup (from Basecamp Launchpad)
BASECAMP_CLIENT_ID=...
BASECAMP_CLIENT_SECRET=...
BASECAMP_REDIRECT_URI=...

# API Access (from OAuth flow)
BASECAMP_ACCOUNT_ID=...
BASECAMP_ACCESS_TOKEN=...
BASECAMP_REFRESH_TOKEN=...
```

---

## Troubleshooting

### Problem: "redirect_uri_mismatch" Error

**Cause:** Redirect URI doesn't match exactly with registered URI

**Solution:**
1. Check your registered URI in Basecamp Launchpad
2. Ensure exact match (including http/https, trailing slashes)
3. Update `.env` with correct URI

### Problem: "invalid_grant" Error

**Causes:**
- Authorization code already used
- Code expired (10 minute limit)
- Mismatched redirect URI

**Solution:**
- Start OAuth flow again from Step 3
- Use authorization code immediately
- Verify redirect URI matches

### Problem: "unauthorized_client" Error

**Cause:** Invalid Client ID or Client Secret

**Solution:**
1. Verify credentials in Basecamp Launchpad
2. Check for typos in `.env` file
3. Ensure no extra spaces or quotes

### Problem: 403 Forbidden on API Requests

**Causes:**
- Token not yet active (wait a few seconds)
- Token expired (14 days)
- Wrong Account ID

**Solution:**
1. Wait 10 seconds and try again
2. Refresh token if expired (Step 8)
3. Verify Account ID matches account in authorization

### Problem: Container Can't Find Files

**Cause:** Docker image not built or vendor directory missing

**Solution:**
```bash
# On Host
make clean
make install
make oauth-flow
```

### Problem: "This site can't be reached" at Callback

**This is normal!** The callback URL doesn't need to be a real server.

**Action:**
1. Don't close the browser
2. Look at the URL bar
3. Copy the code after `?code=`
4. Paste it in the terminal

---

## Security Best Practices

### ✅ DO

- Store Client Secret in environment variables or secure vaults
- Use HTTPS for redirect URIs in production
- Implement state parameter for CSRF protection
- Encrypt tokens in database
- Rotate refresh tokens periodically
- Use secure session storage
- Implement rate limiting

### ❌ DON'T

- Commit `.env` or credentials to version control
- Expose Client Secret in client-side code
- Share tokens publicly
- Use same credentials across environments
- Store tokens in plain text in database
- Hardcode credentials in source code
- Skip token expiration checks

---

## Next Steps

✅ **Setup Complete!** You now have:
- OAuth 2.0 credentials
- Access token for API requests
- Working API client
- Automated token refresh

### Continue Learning

- **API Usage**: See [OAUTH-EXAMPLES.md](./OAUTH-EXAMPLES.md) for code examples
- **Docker Guide**: See [../DOCKER.md](../DOCKER.md) for Docker setup details
- **API Reference**: See official [Basecamp BCX API Docs](https://github.com/basecamp/bcx-api)

### Production Deployment

Before deploying to production:

1. **Update Redirect URI** to production URL (HTTPS required)
2. **Secure credentials** using environment variables or secrets manager
3. **Implement proper error handling** for token expiration
4. **Add logging** for debugging OAuth issues
5. **Test token refresh** mechanism
6. **Set up monitoring** for API rate limits

---

## Additional Resources

- [Basecamp OAuth Documentation](https://github.com/basecamp/api/blob/master/sections/authentication.md)
- [OAuth 2.0 RFC 6749](https://tools.ietf.org/html/rfc6749)
- [OAuth 2.0 Security Best Practices](https://tools.ietf.org/html/draft-ietf-oauth-security-topics)
- [Basecamp 2 API Reference](https://github.com/basecamp/bcx-api)

---

**Need Help?** Open an issue at: https://github.com/schmunk42/php-bcx-client/issues

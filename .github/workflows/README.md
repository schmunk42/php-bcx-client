# GitHub Actions CI/CD

This project uses GitHub Actions for continuous integration and code quality checks.

## Workflows

### CI Workflow (`ci.yml`)

Runs on every push and pull request to main branches.

**Jobs:**

1. **Tests** (`tests`)
   - Runs on PHP 8.4 (can be extended to test multiple PHP versions)
   - Installs dependencies with Composer
   - Executes PHPUnit tests with code coverage (PCOV)
   - Generates coverage report (coverage.xml)
   - Uploads coverage to Codecov (optional)

2. **Code Quality** (`code-quality`)
   - Runs PHPStan static analysis (level 8)
   - Runs PHP-CS-Fixer in dry-run mode to check code style

## Setup Instructions

### 1. Enable GitHub Actions

GitHub Actions should be enabled by default for your repository. No additional setup needed.

### 2. Optional: Enable Codecov Integration

If you want coverage badges and reports on Codecov:

1. Sign up at https://codecov.io with your GitHub account
2. Add your repository to Codecov
3. Get your upload token from Codecov settings
4. Add it as a repository secret:
   - Go to: Repository Settings → Secrets and variables → Actions
   - Click "New repository secret"
   - Name: `CODECOV_TOKEN`
   - Value: Your Codecov upload token

**Note**: The workflow will continue even if Codecov upload fails (using `continue-on-error: true`).

### 3. Badges

Add these badges to your README.md:

```markdown
[![CI](https://github.com/YOUR_USERNAME/php-bcx-api/actions/workflows/ci.yml/badge.svg)](https://github.com/YOUR_USERNAME/php-bcx-api/actions/workflows/ci.yml)
[![codecov](https://codecov.io/gh/YOUR_USERNAME/php-bcx-api/branch/main/graph/badge.svg)](https://codecov.io/gh/YOUR_USERNAME/php-bcx-api)
```

Replace `YOUR_USERNAME` with your GitHub username.

## Why Not Docker in CI?

While this project includes a Docker setup for local development, the CI uses GitHub's native PHP setup because:

1. **Speed**: ~2-3 minutes vs ~15 minutes with Docker building
2. **Cost**: Free GitHub runners are optimized for native builds
3. **Simplicity**: No need to maintain separate CI Dockerfiles
4. **Caching**: Better Composer dependency caching
5. **No external services**: We don't need databases, Redis, etc.

## Features

### Fast Execution
- Composer dependency caching between runs
- PCOV for fast code coverage (faster than Xdebug)
- Parallel job execution (tests + code quality)

### Matrix Testing (Optional)
Uncomment in `ci.yml` to test multiple PHP versions:

```yaml
matrix:
  php: ['8.3', '8.4']
```

### Quality Checks
- **PHPStan Level 8**: Strict static analysis
- **PHP-CS-Fixer**: PSR-12 compliance
- **Code Coverage**: 100% coverage requirement

## Local Testing

Test what will run in CI locally:

```bash
# Install dependencies
composer install

# Run tests with coverage
vendor/bin/phpunit --coverage-text

# Run PHPStan
vendor/bin/phpstan analyze

# Check code style
vendor/bin/php-cs-fixer fix --dry-run --diff
```

## Monitoring

- View workflow runs: Repository → Actions tab
- See coverage trends on Codecov dashboard
- Get notifications for failing builds via GitHub

## Troubleshooting

### Tests fail in CI but pass locally
- Check PHP version differences
- Verify all dependencies are in composer.json (not just composer.lock)
- Check for environment-specific code

### Code style issues
```bash
# Fix locally
vendor/bin/php-cs-fixer fix

# Commit and push
```

### PHPStan errors
```bash
# Run locally with same settings
vendor/bin/phpstan analyze --level=8

# Fix issues or add to phpstan.neon ignoreErrors
```

## Performance

Typical run times:
- Tests job: ~1-2 minutes
- Code quality job: ~1 minute
- Total: ~2-3 minutes

With Docker (for comparison): ~15-20 minutes

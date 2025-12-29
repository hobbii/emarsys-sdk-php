# Emarsys SDK

[![Latest Version on Packagist](https://img.shields.io/packagist/v/hobbii/emarsys-sdk-php.svg?style=flat-square)](https://packagist.org/packages/hobbii/emarsys-sdk-php)
[![Tests](https://img.shields.io/github/actions/workflow/status/hobbii/emarsys-sdk-php/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/hobbii/emarsys-sdk-php/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/hobbii/emarsys-sdk-php.svg?style=flat-square)](https://packagist.org/packages/hobbii/emarsys-sdk-php)

A PHP SDK for the [Emarsys API v3](https://dev.emarsys.com/docs/core-api-reference) with OAuth 2.0 authentication and Contact Lists management.

## Features

- Contact Lists management (Create, List, Get, Delete)
- OAuth 2.0 Client Credentials authentication
- Type-safe DTOs with full PHPDoc coverage
- Comprehensive error handling with custom exceptions
- PSR-12 compliant code style
- Framework-agnostic with optional Laravel support
- Automatic token refresh and management
- PHPStan level 8 static analysis
- 100% unit test coverage

## Requirements

- PHP 8.3 or higher
- GuzzleHttp 7.0 or higher

## Installation

You can install the package via composer:

```bash
composer require hobbii/emarsys-sdk-php
```

## Configuration

Initialize the client with your Emarsys OAuth 2.0 credentials:

```php
use Hobbii\Emarsys\Client;

$client = new Client(
    clientId: 'your-client-id',
    clientSecret: 'your-client-secret'
);
```

## Authentication

The SDK uses OAuth 2.0 Client Credentials flow for authentication. The access token is automatically obtained and refreshed as needed. You don't need to manage tokens manually.

### How it works

1. On first API call, the SDK requests an access token using your `client_id` and `client_secret`
2. The token is cached and automatically included in subsequent requests
3. When the token expires, it's automatically refreshed
4. All authentication is handled transparently

## Usage

### Contact Lists

#### Create a Contact List

```php
use Hobbii\Emarsys\Domain\ContactLists\DTOs\CreateContactList;

$contactList = new CreateContactList(
    name: 'Newsletter Subscribers',
    description: 'List of users subscribed to our newsletter',
);

try {
    $contactListId = $client->contactLists()->create($contactList);

    echo "Created contact list ID: {$contactListId})";
} catch (\Hobbii\Emarsys\Domain\Exceptions\AuthenticationException $e) {
    echo "OAuth authentication failed: {$e->getMessage()}";
} catch (\Hobbii\Emarsys\Domain\Exceptions\ApiException $e) {
    echo "Error: {$e->getMessage()}";
}
```

#### List All Contact Lists

```php
try {
    $contactLists = $client->contactLists()->list();

    foreach ($contactLists as $contactList) {
        echo "ID: {$contactList->id}, Name: {$contactList->name}\n";
    }

    echo "Total contact lists: {$contactLists->count()}";
} catch (\Hobbii\Emarsys\Domain\Exceptions\ApiException $e) {
    echo "Error: {$e->getMessage()}";
}
```

#### Delete a Contact List

```php
try {
    $success = $client->contactLists()->delete(123);

    if ($success) {
        echo "Contact list deleted successfully";
    }
} catch (\Hobbii\Emarsys\Domain\Exceptions\ApiException $e) {
    echo "Error: {$e->getMessage()}";
}
```

## Error Handling

The SDK provides comprehensive error handling with custom exception classes:

- `EmarsysException` - Base exception for all SDK errors
- `ApiException` - General API errors with HTTP status codes and response details
- `AuthenticationException` - Authentication failures
- `RateLimitException` - Rate limit exceeded (429 responses)

```php
use Hobbii\Emarsys\Domain\Exceptions\ApiException;
use Hobbii\Emarsys\Domain\Exceptions\AuthenticationException;
use Hobbii\Emarsys\Domain\Exceptions\EmarsysException;

try {
    $contactLists = $client->contactLists()->list();
} catch (AuthenticationException $e) {
    echo "OAuth authentication failed: {$e->getMessage()}";
    echo "Please check your client_id and client_secret.";
} catch (ApiException $e) {
    echo "API error: {$e->getMessage()}";
} catch (EmarsysException $e) {
    echo "General Emarsys error: {$e->getMessage()}";
}
```

### Rate Limiting

The Emarsys API enforces rate limits to ensure fair usage and maintain system stability. When the rate limit is exceeded, the API returns a 429 (Too Many Requests) response, and the SDK throws a `RateLimitException`.

**Emarsys Rate Limit Headers:**
- `X-RateLimit-Limit`: Request limit per minute
- `X-Ratelimit-Remaining`: The number of requests left for the time window
- `X-RateLimit-Reset`: The time when the rate limit window resets (Unix timestamp)

The exception includes all rate limit information:

```php
use Hobbii\Emarsys\Domain\Exceptions\RateLimitException;

try {
    $contactLists = $client->contactLists()->list();
} catch (RateLimitException $e) {
    // Get information about the rate limit
    echo "Rate limit exceeded!\n";
    echo "Retry after: {$e->retryAfterSeconds} seconds\n";

    if ($e->resetTimestamp !== null) {
        $resetTime = date('Y-m-d H:i:s', $e->resetTimestamp);
        echo "Rate limit resets at: {$resetTime}\n";
    }

    if ($e->limitRemaining !== null) {
        echo "Requests remaining: {$e->limitRemaining}\n";
    }

    if ($e->limitTotal !== null) {
        echo "Total limit: {$e->limitTotal} per minute\n";
    }

    // Wait and retry
    sleep($e->retryAfterSeconds);
    $contactLists = $client->contactLists()->list();
}
```

**Note:** In the current version (v1.0.0-RC1), rate limit handling is manual - you need to catch the exception and implement retry logic yourself. Automatic retry with exponential backoff is planned for a future release. See `RATE_LIMITING_SPEC.md` for details.

## Data Transfer Objects (DTOs)

The SDK uses type-safe DTOs for all data exchange:

## Docker Development Environment

A Docker setup is provided for consistent development and testing across different environments. This is especially useful if you have PHP version or extension conflicts on your local machine.

### Prerequisites

- Docker Desktop or Docker Engine
- Docker Compose (included with Docker Desktop)

### Quick Start with Docker

```bash
# Build and start the container
make up

# Install dependencies
make install

# Run tests
make test

# Run all checks (format, analyse, test)
make check

# Open an interactive shell
make shell
```

### Available Make Commands

```bash
make help            # Display all available commands
make build           # Build the Docker image
make up              # Start the container
make down            # Stop the container
make shell           # Open interactive shell
make install         # Install composer dependencies
make test            # Run PHPUnit tests
make format          # Format code with Pint
make analyse         # Run PHPStan analysis
make check           # Run all checks
make integration-test # Run integration tests
make clean           # Remove containers and volumes
make rebuild         # Clean and rebuild everything
```

### Using Docker Compose Directly

If you prefer to use Docker Compose directly:

```bash
# Start container
docker-compose up -d

# Run tests
docker-compose exec php composer test

# Run PHPStan
docker-compose exec php composer analyse

# Format code
docker-compose exec php composer format

# Open shell
docker-compose exec php sh

# Stop container
docker-compose down
```

### Environment Variables for Integration Tests

To run integration tests with Docker, pass your credentials as environment variables:

```bash
# Set environment variables in your shell
export EMARSYS_CLIENT_ID='your-client-id'
export EMARSYS_CLIENT_SECRET='your-client-secret'

# Or create a .env file (recommended)
cp .env.example .env
# Edit .env with your credentials

# Run integration tests
make integration-test
```

The Docker setup automatically passes through `EMARSYS_*` environment variables to the container.

## Testing

### Unit Tests

```bash
# Run all tests
composer test
# OR
make test

# Run code formatting
composer format
# OR
make format

# Run static analysis with PHPStan
composer analyse
# OR
make analyse

# Run everything (format, analyse, test)
composer check
# OR
make check
```

### Integration Testing with Real Credentials

To test the SDK with your actual Emarsys API credentials:

#### Setup Credentials

Option 1: Environment Variables

```bash
export EMARSYS_CLIENT_ID='your-client-id'
export EMARSYS_CLIENT_SECRET='your-client-secret'
```

Option 2: .env File (Recommended)

```bash
# Copy the template and edit with your credentials
cp .env.example .env
# Edit .env file with your actual credentials
```

#### Quick Test (Read-only, Safe)

```bash
# Run quick connection test
make integration-test test=quick
# OR
composer test-integration quick
# OR
php run-integration-tests.php quick
```

#### Specific Integration Test

```bash
# Run comprehensive test (creates and deletes a test contact list)
make integration-test test=tests/Integration/ContactListsIntegrationTest.php
# OR
composer test-integration tests/Integration/ContactListsIntegrationTest.php
# OR
php run-integration-tests.php tests/Integration/ContactListsIntegrationTest.php
```

The integration test performs these operations:

1. ✅ **Authentication** - Tests OAuth 2.0 login
2. ✅ **List Contact Lists** - Retrieves existing lists (read-only)
3. ✅ **Create Contact List** - Creates a test list
4. ✅ **Get Contact List** - Retrieves the created list by ID
5. ✅ **Delete Contact List** - Cleans up the test list

**Note**: The integration test creates and deletes a test contact list but doesn't affect your existing data.

See `tests/Integration/README.md` for more details.

### Code Quality

The project uses several tools to ensure code quality:

- **PHPUnit**: Unit testing with high coverage
- **PHPStan**: Static analysis at level 8 (strictest)
- **Laravel Pint**: PSR-12 code formatting
- **Type Safety**: Full PHP 8.3 type hints with strict typing

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/spatie/.github/blob/main/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

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

// Optionally specify a custom API base URL
$client = new Client(
    clientId: 'your-client-id',
    clientSecret: 'your-client-secret',
    baseUrl: 'https://custom.emarsys.net/api/v3'
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
    $collection = $client->contactLists()->list();

    foreach ($collection->items as $contactList) {
        echo "ID: {$contactList->id}, Name: {$contactList->name}\n";
    }

    echo "Total contact lists: {$collection->count()}";
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

## Data Transfer Objects (DTOs)

The SDK uses type-safe DTOs for all data exchange:

## Testing

### Unit Tests

```bash
# Run all tests
composer test

# Run code formatting
composer format

# Run static analysis with PHPStan
composer analyse

# Run everything (format, analyse, test)
composer check
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
composer test-integration quick
# OR
php tests/Integration/QuickConnectionTest.php
```

#### Full Integration Test

```bash
# Run comprehensive test (creates and deletes a test contact list)
composer test-integration contact-lists
# OR
php tests/Integration/ContactListsIntegrationTest.php

# Run all integration tests
composer test-integration
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

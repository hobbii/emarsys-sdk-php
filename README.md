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
use Hobbii\Emarsys\DTO\CreateContactListRequest;

$request = new CreateContactListRequest(
    name: 'Newsletter Subscribers',
    description: 'List of users subscribed to our newsletter',
    type: 'static'
);

try {
    $contactList = $client->contactLists()->create($request);

    echo "Created contact list: {$contactList->name} (ID: {$contactList->id})";
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

    foreach ($collection->getContactLists() as $contactList) {
        echo "ID: {$contactList->id}, Name: {$contactList->name}\n";
    }

    echo "Total contact lists: {$collection->count()}";
} catch (\Hobbii\Emarsys\Domain\Exceptions\ApiException $e) {
    echo "Error: {$e->getMessage()}";
}
```

#### List Contact Lists with Filters

```php
try {
    $collection = $client->contactLists()->list([
        'limit' => 10,
        'offset' => 0
    ]);

    // Process the results...
} catch (\Hobbii\Emarsys\Domain\Exceptions\ApiException $e) {
    echo "Error: {$e->getMessage()}";
}
```

#### Get a Specific Contact List

```php
try {
    $contactList = $client->contactLists()->get(123);

    echo "Name: {$contactList->name}\n";
    echo "Description: {$contactList->description}\n";
    echo "Type: {$contactList->type}\n";
    echo "Count: {$contactList->count}\n";
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
    $contactList = $client->contactLists()->get(123);
} catch (AuthenticationException $e) {
    echo "OAuth authentication failed: {$e->getMessage()}";
    echo "HTTP Status: {$e->getHttpStatusCode()}";
    echo "Please check your client_id and client_secret.";
} catch (ApiException $e) {
    echo "API error: {$e->getMessage()}";
    echo "HTTP Status: {$e->getHttpStatusCode()}";
    print_r($e->getResponseBody());
} catch (EmarsysException $e) {
    echo "General Emarsys error: {$e->getMessage()}";
}
```

## Data Transfer Objects (DTOs)

The SDK uses type-safe DTOs for all data exchange:

### ContactList

- `id` (int) - Contact list ID
- `name` (string) - Contact list name
- `description` (?string) - Optional description
- `created` (?string) - Creation timestamp
- `type` (?string) - List type (e.g., 'static', 'dynamic')
- `count` (?int) - Number of contacts in the list

### CreateContactListRequest

- `name` (string) - Contact list name
- `description` (?string) - Optional description
- `type` (?string) - Optional list type

### ContactListCollection

- `contactLists` (ContactList[]) - Array of contact lists
- `meta` (?array) - Optional metadata from API response

## Testing

```bash
composer test
```

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

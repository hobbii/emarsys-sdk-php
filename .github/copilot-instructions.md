# GitHub Copilot Instructions for Emarsys SDK PHP

> **Note:** This file contains essential Copilot-specific instructions. For comprehensive development guidelines, see [.cursor/rules/main-instructions.mdc](../.cursor/rules/main-instructions.mdc)

## Quick Reference

- **Language:** PHP 8.3+ with strict typing (`declare(strict_types=1)`)
- **Code Style:** PSR-12 (auto-formatted with Laravel Pint)
- **Architecture:** Domain-driven with immutable Value Objects and DTOs
- **Testing:** PHPUnit with comprehensive unit/integration tests
- **Quality:** PHPStan level 8, high test coverage

## Essential Rules for Code Generation

### 1. Type Safety (Critical)
```php
<?php

declare(strict_types=1);

// All parameters and returns MUST be typed
public function create(CreateContactList $dto): ContactList
{
    // ...
}
```

### 2. Immutable Classes
```php
readonly class ContactList
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $description,
    ) {}
}
```

### 3. Project Structure
- `src/Domain/Feature/` - business logic (e.g., `ContactLists/`)
- `src/Domain/Feature/DTOs/` - input objects
- `src/Domain/Feature/ValueObjects/` - output objects
- Tests mirror source structure

### 4. Exception Hierarchy
```php
EmarsysException (base)
├─> AuthenticationException
├─> RateLimitException
└─> ApiException
```

### 5. Testing Pattern
```php
public function test_descriptive_action_and_expected_outcome(): void
{
    // Arrange
    $client = $this->createClientWithMockHandler([/* responses */]);

    // Act
    $result = $client->someMethod();

    // Assert
    $this->assertInstanceOf(ExpectedClass::class, $result);
}
```

## Commands (Use Docker)
- `make test` - run PHPUnit tests
- `make analyse` - PHPStan static analysis
- `make format` - Laravel Pint formatting
- `make check` - all quality checks

## When Generating Code

1. **Always include `declare(strict_types=1)`**
2. **Use `readonly` for immutable classes**
3. **Generate corresponding unit tests**
4. **Add PHPDoc for public methods**
5. **Follow naming: `PascalCase` classes, `camelCase` methods**
6. **Handle errors with specific exceptions**

For detailed patterns, architecture decisions, Docker workflow, and troubleshooting, refer to the comprehensive guide in `.cursor/rules/main-instructions.mdc`.

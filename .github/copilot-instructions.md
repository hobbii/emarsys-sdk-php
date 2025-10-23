# GitHub Copilot Instructions for PHP Package

## General
- Language: **PHP 8.3**
- Frameworks: **Laravel 10** (optional, core logic should be framework-agnostic)
- Code Style: **PSR-12**
- Principles: **SOLID, KISS, DRY, Clean Architecture**
- Strict typing: **enable `declare(strict_types=1)`**
- Visibility: **prefer private/protected, public only for API**

## Testing
- Framework: **PHPUnit**
- Coverage: **high**
- Mocks: **use Mockery or PHPUnit built-in mocks**
- Generate **unit and integration tests** for all classes
- Use `composer test` to run tests
- Use `composer analyze` for static analysis (PHPStan)
- Use `composer format` for code formatting (PHP CS Fixer or Laravel Pint)

## Documentation
- Use **PHPDoc for all classes and methods**, but not for trivial getters/setters
- Provide **usage examples** in the README

## Class and Folder Structure
- `src/Domain/` – core business logic
- `src/Laravel/` – service providers, facades, Laravel-specific logic
- `src/DTO/` – value objects and data transfer objects
- `tests/Unit/` – unit tests
- `tests/Integration/` – integration tests

## API Clients
- Use **GuzzleHttp** for HTTP requests
- Handle errors with **exceptions**
- Optionally support **retry strategies** for external APIs

## Best Practices
- Git branches:
  - `main` – stable production
  - `feature/*` – new features
  - `fix/*` – bug fixes
  - `hotfix/*` – urgent fixes
  - `chore/*` – maintenance tasks
- Commit messages: **Conventional Commits**
- Releases: **semantic versioning (vX.Y.Z)**

## Coding Style
- All functions and methods should follow **single responsibility principle**
- Use **type hints** for all parameters and return types
- Keep code **framework-agnostic** unless explicitly in Laravel layer

## Examples
- Provide **code snippets** in Copilot suggestions
- Include **unit test examples** along with class suggestions

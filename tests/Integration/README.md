# Integration Tests

This directory contains integration tests that work with real Emarsys API credentials.

## Test Files

### `QuickConnectionTest.php`

- **Purpose**: Quick authentication and connectivity test
- **Operations**: Read-only (safe for production)
- **What it does**: Lists existing contact lists
- **Use case**: Verify credentials and basic API connectivity

### `ContactListsIntegrationTest.php`

- **Purpose**: Comprehensive CRUD operations test
- **Operations**: Create, Read, Update, Delete
- **What it does**: Creates a test contact list, verifies it, then deletes it
- **Use case**: Full API functionality verification

## Running Tests

### Option 1: Use the Test Runner (Recommended)

```bash
# Set credentials
export EMARSYS_CLIENT_ID='your-client-id'
export EMARSYS_CLIENT_SECRET='your-client-secret'

# Run all tests
php run-integration-tests.php

# Run specific test
php run-integration-tests.php quick
php run-integration-tests.php contact-lists
```

### Option 2: Run Individual Tests

```bash
# Quick test
php tests/Integration/QuickConnectionTest.php

# Full test
php tests/Integration/ContactListsIntegrationTest.php
```

### Option 3: Using .env File

```bash
# Copy template and edit
cp .env.example .env
# Edit .env with your credentials

# Then run tests normally
php run-integration-tests.php
```

## Safety Features

- **Environment Variables**: Credentials are loaded from environment, not hardcoded
- **Safe Fallbacks**: Scripts refuse to run with placeholder credentials
- **No Production Impact**: Creates only test data that gets cleaned up
- **Read-only Option**: Quick test performs no write operations

## Credentials

You need OAuth 2.0 credentials from your Emarsys account:

- `EMARSYS_CLIENT_ID`: Your OAuth client ID
- `EMARSYS_CLIENT_SECRET`: Your OAuth client secret

## Expected Output

### Quick Test

```bash
Testing Emarsys SDK...

✅ Client created successfully
🔍 Testing API connection by listing contact lists...

🎉 SUCCESS! API connection working.
📊 Found 5 contact lists in your Emarsys account.

📝 Your contact lists:
   - Newsletter Subscribers (ID: 123) (1500 contacts)
   - VIP Customers (ID: 456) (250 contacts)
   ...

Done.
```

### Full Integration Test

```bash
🧪 Emarsys SDK Integration Test
================================

1️⃣  Initializing Emarsys client...
   ✅ Client initialized successfully

2️⃣  Testing: List existing contact lists...
   ✅ Successfully retrieved contact lists
   📊 Found 5 contact lists

[... continues with create, read, delete operations ...]

🎉 Integration Test Results
===========================
✅ OAuth 2.0 Authentication: SUCCESS
✅ List Contact Lists: SUCCESS
✅ Create Contact List: SUCCESS
✅ Get Contact List by ID: SUCCESS
✅ Delete Contact List: SUCCESS

🚀 All tests passed! Your Emarsys SDK is working correctly.
```

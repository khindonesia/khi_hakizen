# Testing Guidelines & Practices

This document outlines the testing architecture, frameworks, conventions, and execution commands for the KHI Hakizen application.

## 1. Testing Frameworks

- **Primary Unit/Feature Testing**: **Pest PHP `^3.4`** (running on top of PHPUnit `^11.0`).
- **End-to-End Browser Testing**: **Laravel Dusk `^8.0`** (using `Tests\DuskTestCase`).

## 2. Test Suites Directory Structure

Tests are organized under the `tests/` root directory:

```
tests/
├── Browser/         # Dusk E2E browser tests (UI and interactive flows)
├── Datasets/        # Pest shared dataset files for parameterizing test cases
├── Feature/         # Component integrations, routes, and controllers tests
├── Unit/            # Isolated unit logic and pure helper tests
├── Pest.php         # Pest configuration (binding traits, TestCase mappings, hooks)
└── TestCase.php     # Base Feature/Unit TestCase class
```

## 3. Core Testing Conventions

### A. File Names & Locations
- Test files must be suffixed with `Test.php` (e.g. `XenditCheckoutTest.php`, `AspirasiTest.php`).
- Place feature tests inside `tests/Feature/` mapping closely to their domain (e.g., checkout flow, cart logic).

### B. Database Operations
- All feature tests bound to `Tests\TestCase::class` use the `Illuminate\Foundation\Testing\RefreshDatabase` trait.
- A fresh database migration is executed and a full database seed (`$this->artisan('db:seed')`) runs **before each test case** in the `beforeEach()` hook defined in `tests/Pest.php`.

### C. Livewire & Filament Testing
- When writing tests for Filament resources or Livewire Volt components, assert behavior using Livewire testing helpers:
  ```php
  use function Pest\Livewire\livewire;

  it('can view list of products', function () {
      livewire(ProductResource\Pages\ListProducts::class)
          ->assertCanSeeTableRecords($products);
  });
  ```
- Always make sure you authenticate users before testing protected actions.

### D. Mocking External Gateways
- Always mock external APIs (e.g. Xendit, RajaOngkir) to ensure test suites are fast, deterministic, and do not hit external endpoints:
  - Utilize `Http::fake()` to mock outbound requests.
  - Verify that callbacks are handled accurately by posting mock payloads to `/api/xendit/callback`.

## 4. Run Commands

To run the full test suite or specific tests, use the following commands:

```bash
# Run the entire Pest test suite
php artisan test

# Run tests in compact mode
php artisan test --compact

# Filter and run a specific test file
php artisan test --compact --filter=XenditCheckoutTest

# Run Dusk browser tests
php artisan dusk
```

> **IMPORTANT**: Every code change must be programmatically verified. Do NOT delete existing tests without explicit approval.

---
*Last updated: 2026-05-26 after initialization*

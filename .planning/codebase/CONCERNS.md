# Codebase Concerns & Fragile Areas

This document captures technical debt, performance considerations, fragile areas, security considerations, and potential bottlenecks in the KHI Hakizen application.

## 1. Technical Debt & Complexity

- **Upgraded Traditional Layout (Laravel 10 structure on Laravel 12 runtime)**:
  - The project utilizes traditional service wiring (`app/Http/Kernel.php`, `app/Console/Kernel.php`, `app/Exceptions/Handler.php`) instead of the new streamlined Laravel 11/12 structures.
  - While recommended by Laravel for upgraded codebases, developers must remember not to look for `bootstrap/app.php` or inline middleware registrations in routes.
- **Stripe & Xendit Gateway Splits**:
  - The coexistence of Stripe (used by the default Wave SaaS subscription engine) and Xendit (used for Indonesian storefront checkouts and event bookings) introduces high complexity in order state tracing. 
  - Ensure that actions modifying order status or user subscription states respect the gateway-specific database fields (e.g. `external_id`, `invoice_id`).

## 2. Performance Bottlenecks & Caching

- **Indonesian Administrative Database Seeders**:
  - The `laravolt/indonesia` region tables contain thousands of records. Since feature tests run `$this->artisan('db:seed')` on every test run, test suite execution can become slow if the seeders are not highly optimized or if they attempt to write all administrative village boundaries during standard test setup.
- **RajaOngkir Cache Dependency**:
  - Outbound logistics requests to RajaOngkir Komerce endpoints (`destination/domestic-destination`, etc.) have a high latency overhead.
  - Caching is implemented in `RajaOngkirLocationLookup`, but if the cache gets bypassed or cleared frequently, checkout latency will spike. Keep cache TTLs reasonably high.
- **N+1 Queries in Filament Resources**:
  - Custom resources such as `ProductResource` and `OrderResource` map models with rich relational hierarchies (e.g. Products with Variants, Attributes, Categories, Images).
  - High vigilance is needed to enforce eager loading (`with()`) on Filament tables to prevent database request overloading on back-office lists.

## 3. Fragile & Fragile Integration Areas

- **Theme Page Folio Routing & Volt Mounts**:
  - Folio pages mounted from active themes (`resources/themes/anchor/pages`) reside outside the traditional `resources/views/pages` folder.
  - Adding pages requires them to be registered to the custom theme. If a developer accidentally adds them under standard views, Folio will fail to resolve the path.
- **Tymon JWT & Session Authentication Coexistence**:
  - `composer.json` requires `tymon/jwt-auth` in tandem with traditional session-based cookies.
  - Ensure that web-facing storefront views do not mix session logic with JWT payload expectations unless explicitly bridged.

## 4. Testing & CI Limitations

- **Dusk Browser Testing Chrome Driver Dependencies**:
  - Dusk browser tests (`tests/Browser/`) are highly dependent on the host environment's Google Chrome and chrome-driver versions.
  - Running browser tests in headless CI environments requires exact alignment of Chrome versions to prevent Dusk connection exceptions.

---
*Last updated: 2026-05-26 after initialization*

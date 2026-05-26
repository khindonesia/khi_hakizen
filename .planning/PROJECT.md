# Project: KHI Hakizen

## What This Is

KHI Hakizen is a comprehensive digital portal and community hub for Komunitas Hakizen Indonesia. It features a robust e-commerce merchandise storefront, a digital publication library (Ebooks), an interactive event ticketing and attendance manager, an organizational portal, and a transparent citizen-aspirations feedback system. It is designed to be highly modular, reactive, and localized for Indonesia.

## Core Value

Provide a fast, highly reactive, and integrated digital hub for the Hakizen Indonesia community to manage memberships, explore publications, book events, and purchase official merchandise.

## Requirements

### Validated

- ✓ **AUTH-01** (SaaS Core): Standard customer and admin authentication with profile management and role authorization (via Wave & Spatie Permissions).
- ✓ **MERCH-01** (E-Commerce): Structured merchandise catalog supporting products, categories, variants, and product attributes.
- ✓ **CART-01** (E-Commerce): Interactive shopping cart management with dynamic pricing and item controls.
- ✓ **LOGIS-01** (Logistics): Localized delivery address management with automatic lookup of Indonesian Provinces, Cities, Districts, and Villages (via Laravolt and RajaOngkir).
- ✓ **PAY-01** (Checkout): Automated domestic payment checkouts generating digital invoices (via Xendit API gateway callback sync).
- ✓ **EVENT-01** (Community): Event ticketing, attendance, and registration portal supporting both free and paid events.
- ✓ **EBOOK-01** (Library): Digital publications library allowing authenticated users to read/access Ebook resources.
- ✓ **ASPIR-01** (Aspirations): Public feedback submission system (Aspirasi) for community outreach.
- ✓ **ADMIN-01** (Back-Office): Full-fledged administrative panel (via Filament) giving complete back-office CRUD controls over all entities.

### Active

- [ ] **INIT-01**: Complete GSD workspace initialization, including full codebase mapping and roadmap structure.
- [ ] **VERIFY-01**: Run and verify all 26 existing feature tests to confirm application stability on this environment.

### Out of Scope

- **Laravel Streamlined Layout Migration** — The project has upgraded to Laravel 12 but maintains the traditional Laravel 10/Wave layout. Do not reorganize directory structures (such as moving HTTP Middlewares to `bootstrap/app.php` or console commands to `routes/console.php`) unless explicitly requested by the user.

## Context

- **Technical Environment**: Laravel 12, PHP 8.5.6, Filament v3, Livewire v3, Volt v1, Pest v3, Dusk v8, Tailwind CSS v3.
- **Framework Upgrade**: The codebase has successfully completed the upgrade path to Laravel 12 while preserving its original modular structure.
- **Localizations**: Strongly integrated with Indonesian localizations (Laravolt region tables, Xendit payment webhooks, RajaOngkir shipping calculation service).

## Constraints

- **Compatibility**: Must conform to traditional Laravel file structure mapping due to dependency bindings in Wave and Folio Providers.
- **Testing**: Every new feature or fix must be covered by a Pest feature test. Do not delete existing tests.
- **Design system**: The frontend utilizes Tailwind CSS v3 and Alpine.js v3 inside active theme folders (`resources/themes/anchor/`). Do not write custom inline CSS or import external styling frameworks without authorization.

## Key Decisions

| Decision | Rationale | Outcome |
|----------|-----------|---------|
| Retain Laravel 10 file structure | Avoid breaking directory and namespace assumptions in Wave provider boot | ✓ Good |
| Mount Folio & Volt to Theme | Allows complete theme decoupling and dynamic loading of front-facing storefront views | ✓ Good |
| Action-Service decoupling | Separates domain business transactions from API client requests for test mockability | ✓ Good |

## Evolution

This document evolves at phase transitions and milestone boundaries.

**After each phase transition** (via `/gsd-transition`):
1. Requirements invalidated? → Move to Out of Scope with reason
2. Requirements validated? → Move to Validated with phase reference
3. New requirements emerged? → Add to Active
4. Decisions to log? → Add to Key Decisions
5. "What This Is" still accurate? → Update if drifted

**After each milestone** (via `/gsd-complete-milestone`):
1. Full review of all sections
2. Core Value check — still the right priority?
3. Audit Out of Scope — reasons still valid?
4. Update Context with current state

---
*Last updated: 2026-05-26 after initialization*

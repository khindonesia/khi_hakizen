# Codebase Directory Structure & File Map

This document maps out the specific file locations, directory hierarchies, and primary directories within the KHI Hakizen workspace.

## 1. Directory Tree Map

Here is the high-level representation of critical directories:

```
khi_hakizen/
├── app/
│   ├── Actions/                  # Isolated business workflows
│   ├── Console/                  # Console commands & task scheduling
│   ├── Exceptions/               # Laravel exceptions & rendering overrides
│   ├── Filament/                 # Filament admin panel configuration
│   │   ├── Actions/              # Filament actions & modals
│   │   ├── Pages/                # Custom back-office dashboard views
│   │   └── Resources/            # CRUD management resource definitions
│   ├── Http/
│   │   ├── Controllers/          # Cart, Checkout, Xendit, Invoices, RajaOngkir APIs
│   │   ├── Middleware/           # Request throttling & access policies
│   │   └── Requests/             # Form request validators (if any)
│   ├── Listeners/                # Event observers (e.g. Email notification triggers)
│   ├── Models/                   # Eloquent models (Product, Order, Event, User, Address)
│   ├── Providers/                # Service providers (Folio, Route, Filament, App bootstrapping)
│   └── Services/                 # API connection gateways
├── bootstrap/                    # Framework cache & startup wiring
├── config/                       # Central application configurations
├── database/
│   ├── factories/                # Faker model generators for Pest testing
│   ├── migrations/               # Database structure migration scripts
│   └── seeders/                  # Static system data seeds (Regions, Roles, Admin)
├── resources/
│   ├── css/                      # Main stylesheets (Tailwind imports)
│   ├── themes/                   # Theme configurations
│   │   └── anchor/               # Core marketing and customer storefront theme
│   │       ├── assets/           # Compiled theme CSS & JS assets
│   │       ├── components/       # Storefront components (Blade & Volt)
│   │       ├── partials/         # Navbars, footers, sidebars
│   │       └── pages/            # Laravel Folio routes & Volt page templates
│   └── views/                    # Shared fallback views and vendors overrides
├── routes/                       # Web overrides, API webhooks, Channel broadcasts
├── storage/                      # Dynamic files, logs, and compiled cache
├── tests/                        # Automated Pest & Dusk testing suites
│   ├── Browser/                  # Dusk end-to-end browser feature specs
│   ├── Datasets/                 # Shared data arrays for Pest runs
│   ├── Feature/                  # HTTP route and Livewire state integration tests
│   └── Unit/                     # Standalone helper unit tests
├── wave/                         # Wave SaaS Starter Kit engine root
└── vite.config.js                # Vite frontend bundler config
```

## 2. Critical Paths & Naming Conventions

### Models
- Located in: `app/Models/`
- Naming: **PascalCase**, **Singular** (e.g., `Product.php`, `OrderItem.php`, `UserAddress.php`, `EventUser.php`).
- Attributes & relationships are defined fluently inside the models using standard PHP type hints.

### Controllers
- Located in: `app/Http/Controllers/`
- Naming: **PascalCase**, suffixed with `Controller` (e.g., `CartController.php`, `XenditController.php`).
- Actions are kept short, typically forwarding request inputs directly to **Actions** classes or database queries.

### Business Actions
- Located in: `app/Actions/`
- Naming: **PascalCase**, suffixed with `Action` (e.g., `CreateCheckoutInvoiceAction.php`, `HandleXenditWebhookAction.php`).
- These follow the single-responsibility pattern with a public `handle()` method.

### Custom Services
- Located in: `app/Services/`
- Naming: **PascalCase**, suffixed with `Service` or `Gateway` (e.g., `RajaOngkirShippingService.php`, `XenditInvoiceGateway.php`).

### Marketing & Storefront Pages (Folio Routes)
- Located in: `resources/themes/anchor/pages/`
- Naming: **Folio file-based naming** (e.g., `merchandise/index.blade.php` represents `/merchandise`, `merchandise/[Product-slug].blade.php` represents route parameter model binding).

---
*Last updated: 2026-05-26 after initialization*

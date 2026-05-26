# Architectural Patterns & System Design

This document describes the architectural layout, core design patterns, layers, and data flow of the KHI Hakizen application.

## 1. Architectural Philosophy

KHI Hakizen is structured as a robust, hybrid Laravel SaaS application combining the modularity of the **Wave SaaS Kit**, the reactive interactivity of **Livewire & Volt**, the elegance of page-based **Folio Routing**, and the high-productivity admin management of **Filament PHP**. 

The system isolates business logic from controllers by utilizing the **Action Pattern** and abstracts external client operations via **Services & Gateways**.

## 2. Directory Layout & Core Layers

The project uses the traditional Laravel 10 directory structure (upgraded to Laravel 12 runtime compatibility):

```
khi_hakizen/
├── app/
│   ├── Actions/         # Business logic workflow classes (Action Pattern)
│   ├── Console/         # Artisan commands and scheduling
│   ├── Exceptions/      # Custom application exception handlers
│   ├── Filament/        # Server-Driven UI panels, Resources, Pages, and Actions
│   ├── Http/            # Controllers, Request Validation, and Middleware
│   ├── Listeners/       # Asynchronous/Synchronous system event observers
│   ├── Models/          # Eloquent entities & relationship mapping
│   ├── Providers/       # Service providers wiring system bootstrap phases
│   └── Services/        # Client API gateways and standalone utilities
├── config/              # Central configuration files
├── database/            # Migrations, seeders, and factories
├── resources/           # Frontend resources
│   ├── themes/          # Theme directory containing pages, layouts, and assets
│   │   └── anchor/      # Active "Anchor" theme folder (Folio pages and Volt components)
│   └── views/           # Reusable Blade fragments and component layouts
├── routes/              # Web, API, console, and broadcast routes
├── wave/                # Core Wave SaaS Engine dependencies
└── tests/               # Pest & Dusk test suites
```

## 3. Core Design Patterns

### A. Action Pattern (Command Pattern)
Crucial business workflows are encapsulated in single-responsibility **Action** classes. This ensures code is highly reusable, testable, and isolated from HTTP request controllers.
- `App\Actions\CreateCheckoutInvoiceAction` — Compiles cart items, creates an order database entry, and packages line items.
- `App\Actions\CreateUserAddressAction` — Handles validation and creation of user delivery addresses.
- `App\Actions\HandleXenditWebhookAction` — Processes payment webhook notifications from Xendit and triggers state changes.
- `App\Actions\PublishAspirasiAction` — Handles publishing citizen aspirational feedback entries.
- `App\Actions\UpdateUserAddressAction` — Performs geographic validation and updates user delivery information.

### B. Service & Gateway Pattern
Interaction with external REST APIs is decoupled using standalone **Service** and **Gateway** objects:
- `App\Services\XenditInvoiceGateway` — Interfaces with Xendit API to generate digital invoices.
- `App\Services\RajaOngkirShippingService` — Handles shipping rates calculations and logistics communication.
- `App\Services\MediaDirectoryGuard` — Handles file upload path permissions and security validation.

### C. Folio Page-Based Routing & Theme Integration
Instead of manual routes mapping in `routes/web.php` for every static or transactional page, routing is handled dynamically:
- **Service Provider**: `App\Providers\FolioServiceProvider` reads the current theme from `theme.json` (defaults to `anchor`).
- **Folio Paths**: Mounts the active theme's pages folder `resources/themes/{themeName}/pages` to Laravel Folio.
- **Volt Mounting**: Automatically registers Livewire Volt functional/class-based components located directly within those page blade files.

### D. Server-Driven UI (Filament Panels)
The administrative back-office utilizes Filament Resources located in `app/Filament/Resources/`.
- Forms and Tables are written entirely in PHP and rendered on the client dynamically.
- Relational mapping is loaded eagerly using Filament's internal schema-binding to prevent N+1 query overhead.

---
*Last updated: 2026-05-26 after initialization*
